<?php

namespace Modules\Task\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Modules\Project\Entities\Project;
use Modules\VkGroup\Entities\VkGroup;
use Modules\Task\Entities\Task;
use Modules\Task\Entities\TaskData;

use Modules\VkApi\Http\Controllers\VkApiController;

use Illuminate\Support\Str;

class TaskGroupsSearchController extends Controller
{
  public $controller_data = [
    ''
  ];

  public $VkApiController;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth'/*, ['except' => ['index','show']] */);
    
    $this->VkApiController = new VkApiController;
  }

  /**
   * Display a listing of the resource.
   * @return Response
   */
  public function index()
  {
    $tasks = Task::all();
    return view('task::groups-search.index')->with('tasks', $tasks);
  }

  /**
   * Show the form for creating a new resource.
   * @return Response
   */
  public function create(Request $request)
  {
    $project = Project::find($request->project_id);
    return view('task::groups-search.create')->with('project', $project);
  }

  /**
   * Store a newly created resource in storage.
   * @param Request $request
   * @return Response
   */
  public function store(Request $request)
  {
    $this->monitor = [];
    $this->monitor['time_start'] = time(true);

    $this->validate($request, [
      'search_query' => 'required',
    ]);

    $search_types = [
      0 => 'По-умолчанию',
      1 => 'Макс.',
    ];

    $task = new Task;
    $task->name = $request->name?: "Поиск по группам — «".$request->search_query."»";
    $task->project_id = $request->project_id;
    $task->task_key = "groups-search";
    $task->status = "CREATED";
    $task->save();

    $this->task_id = $task->id;
    
    foreach(['search_query','group_search_type','limit'] as $key){
      $td = new TaskData;
      $td->task_id = $task->id;
      $td->key = $key;
      $td->value = $request->{$key};
      $td->save();
    }
    $this->groupsAdded = [];
    $api_result = $this->addGroupsToDB([
      "q" => $request->search_query,
      "count" => 1000,
      "offset" => 0,
      ]);
    if($api_result !== true){
      $td = new TaskData;
      $td->task_id = $task->id;
      $td->key = "api_result_error";
      $td->value = $api_result;
      $td->save();
      $task->status = "HAS_ERROR";
      $task->save();
      return redirect()->route('project.show', $request->project_id)->with('error', "<strong>Ошибка</strong><pre>".$api_result."</pre>");
    }else{
      $td = new TaskData;
      $td->task_id = $task->id;
      $td->key = "group_count";
      $td->value = count($this->groupsAdded);
      $td->save();
      $task->status = "DONE_PARSED";
      $task->name = $task->name. " (".count($this->groupsAdded)." гр.)";
      $task->time = time(true) - $this->monitor['time_start'];
      $task->save();
      return redirect()->route('project.show', $request->project_id)->with('success', "Задача выполнена корректно");
    }

    
  }

  /**
   * Show the specified resource.
   * @param int $id
   * @return Response
   */
  public function show(Request $request)
  {  
    $task = Task::with(['project','task_data','vk_groups'])->where('id',$request->task_id)->first();
    //$task->vk_groups = $task->vk_groups->sortBy('sort_order');

    //dd($task->vk_groups);
    $task_data = []; // search_query, group_search_type, limit
    foreach($task->task_data as $td){
      $task_data[$td->key] = $td->value;
    }
    if($task->project->user_id != auth()->user()->id)
    {
      return redirect()->route('task.index')->with('error', "<strong>Ой!</strong> Доступ ограничен");
    }
    $words = [];
    $snts = [];
    $delimers = ['|',',','.','/'];
    foreach($task->vk_groups as $group){
      $group_name = Str::lower($group->name);
      foreach($delimers as $delimer){
        $group_name = str_replace('  ',' ', str_replace($delimer, ' ', $group_name));
      }

      $group_name_ex = explode(" ", $group_name);
      foreach($group_name_ex as $k => $word){        

        $ta = [$word];
        if(!empty($group_name_ex[$k+1]))
        {
          $ta[] = $group_name_ex[$k+1];
          if(Str::length($group_name_ex[$k+1]) < 3)
          {
            if(!empty($group_name_ex[$k+2]))
            {
              $ta[] = $group_name_ex[$k+2];
            }
          }
          if(count($ta) > 1){
            $ta = implode(" ", $ta);
            if(empty($snts[$ta])){
              $snts[$ta] = 1;
            }else{
              $snts[$ta] += 1;
            }
          }
        }

        if(Str::length($word) > 1)
        {
          if(empty($words[$word])){
            $words[$word] = 1;
          }else{
            $words[$word] += 1;
          } 
        }
      }
    }
    arsort($snts);
    dd($snts);




    return view('task::groups-search.show', ['task' => $task, 'task_data' => $task_data]);
  }

  // Отрефакторить нужно в соответствии с SDK
  public function addGroupsToDB($params){

    // {"q":"Сварка","count":250,"offset":0}
    $code = 'var result = [];'."\n";
    $code .= 'var sq = "'.$params['q'].'";';
    

    // Чтобы получить максимум результатов
    foreach(['group', 'page'] as $type)
    {
      foreach([0,1,2,3,4,5] as $sort_order)
      {
      $code .= 'result.push({"sort_type":'.$sort_order.', "res":API.groups.search({"q":sq,"count":1000,"offset":0,"type":"'.$type.'","sort":'.$sort_order.'})});'."\n";
      }
    }
    $code .= 'return result;';
    //return "<pre>".$code."</pre>";
    $result = json_decode($this->VkApiController->vkr('execute', [
      "code" => sprintf($code, json_encode($params, 256))
    ]), true);
      
    $need_more = false;

    if(!empty($result['error'])){
      return print_r($result['error'], true);
    }
    $sort_order = 1;
    foreach($result['response'] as $chunk){
      $sort_type = $chunk['sort_type'];
      $groups_to_insert = [];
      $task_vk_group_to_insert = [];
      foreach($chunk['res']['items'] as $item){     
      if($item['is_closed']){
        continue;
      }
      
      $validator = Validator::make(
        [
        'id' => $item['id']
        ],
        [
        'id' => 'unique:vk_groups'
        ]);
      
      if($validator->passes()){
        $groups_to_insert[] = [
        'id' => $item['id'],
        'name' => $item['name'],
        'screen_name' => $item['screen_name'],
        'type' => $item['type'],
        'sort_type' => $sort_type,
        'is_closed' => $item['is_closed'],
        'created_at' => now(),
        'updated_at' => now(),
        ];
      }
      $this->groupsAdded[$item['id']] = '';

      $task_vk_group_to_insert[] = ["vk_group_id" => $item['id'], "task_id" => $this->task_id, 'sort_order' => $sort_order++];
      }
      VkGroup::insertOrIgnore($groups_to_insert);
      DB::table('task_vk_group')->insertOrIgnore($task_vk_group_to_insert);
    }
    if($need_more){
      usleep(350000);
      return $this->addGroupsToDB(array_merge($params, ['offset' => $need_more]));
    }else{
      return true;
    }
  }
}
