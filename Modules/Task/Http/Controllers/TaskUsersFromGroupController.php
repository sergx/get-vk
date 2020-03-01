<?php

namespace Modules\Task\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Modules\Project\Entities\Project;

use Modules\Task\Entities\Task;
use Modules\Task\Entities\TaskData;

use Modules\VkGroup\Entities\VkGroup;

use Modules\VkUser\Entities\VkUser;
use Modules\VkUser\Entities\VkUserFirstName;
use Modules\VkUser\Entities\VkUserLastName;
use Modules\VkUser\Entities\VkUserBdate;
use Modules\VkUser\Entities\VkUserUnivFast;

use Modules\VkApi\Http\Controllers\VkApiController;

class TaskUsersFromGroupController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth'/*, ['except' => ['index','show']] */);
    $this->monitor = [];
    $this->monitor['time_start'] = time(true);
    $this->VkApiController = new VkApiController;
  }

  public function create(Request $request)
  {
    $project = Project::with(['tasks','tasks.task_data'])->find($request->project_id);
    $group_search_tasks = $project->tasks->where('task_key', 'groups-search')->all();

    $task_data = new \stdClass;
    foreach($group_search_tasks as $key => $task){
      $ta = new \stdClass;
      foreach($task->task_data as $td){
        $ta->{$td->key} = $td->value;
      }
      $task_data->{$task->id} = $ta;
    }

    return view('task::users-from-group.create', ['project' => $project, 'group_search_tasks' => $group_search_tasks, 'task_data' => $task_data]);
  }

  public function show(Request $request)
  {
    $task = Task::with(['project','task_data'])->where('id', $request->task_id)->first();

    $groups_search_task_id = $task->task_data->where('key', 'groups_search_task_id')->pluck('value')->first();
    $groups_from_task = Task::find($groups_search_task_id);
    //$user_ids_collected = DB::table('orders')->where()->sum('price');
    $vk_groups = $groups_from_task->vk_groups();
    $stat_data = [];
    $stat_data['user_ids_collected'] = $vk_groups->sum('users_collected');
    $stat_data['total_groups_count'] = $vk_groups->count();
    $stat_data['filled_groups_count'] = $vk_groups->where("users_count", ">", "0")->count();
    $stat_data['closed_groups_count'] = $vk_groups->where("is_closed", "1")->count();
    $stat_data['closed_groups_count_with_open_ids'] = $vk_groups->where("is_closed", "1")->where("users_count", ">", "0")->toSql();


    return view('task::users-from-group.show', ['task' => $task, 'stat_data' => $stat_data,]);
  }

  public function store(Request $request)
  {
    $groups_from_task = Task::find($request->group_search_task_id);

    $parse_users_task = new Task;
    $parse_users_task->name = "Парсинг UserIds для «". $groups_from_task->name."»";
    $parse_users_task->project_id = $request->project_id;
    $parse_users_task->task_key = "users-from-group";
    $parse_users_task->status = "CREATED";
    $parse_users_task->save();

    $td = new TaskData;
    $td->task_id = $parse_users_task->id;
    $td->key = "groups_search_task_id";
    $td->value = $request->group_search_task_id;
    $td->save();

    return redirect()->route('task.users-from-group.prossess', [
      'project_id' => $request->project_id,
      'parse_users_task_id' => $parse_users_task->id,
      //'group_search_task_id' => $request->group_search_task_id,
    ])->with('success', "Приступаем к задаче <strong>".$parse_users_task->name."</strong>");

    // В execute методе может быть не больше 25 запросов к API
    // максимум 1000 можно выбрать за 1 запрос
    // В группе может быть более 25000 человек, а может быть и меньше.
    // Нужно такой скрипт написать, который и большую группу смог бы спарсить, и маленькие тоже
    // Может быть ошибка - закончились лимиты обращения к API или другая ошибка, или нулевой результат
    // Возвращается список пользователей с более-менее широкими данными
    // Пользователь может быть удален - "deactivated": "deleted",

    // Можно собирать первой итерацией кол-во участников и первыую тысячу, а второй итерацией - уже всех остальных участников. Так по меньшей мере будет выборка последней тысячи вступивших, и не нужно городить жесть в js execute API.
  }
  public function API_groups_getMembers($settings){

    $groups = $settings['groups'];
    $count = $settings['count'] ?: 500;
    $limit = $settings['limit'] ?: 15;
    $fields = $settings['fields'] ?: '';
    $offset_key = $settings['offset_key'] ?: '';

    // $groups_in_cache = [];
    if(session("reduce_limit"))
    {
      $limit = session("reduce_limit");
    }
    $exec    = "var result = [];";
    $exec   .= "var gr = [];";
    foreach($groups as $item){
      // $cache_key = md5($count . $fields . $item->id . intval($item->users_parsed));
      // if(!Cache::has($cache_key))
      // {
      if($limit > 0 && (!$item->users_count || $item->{$offset_key} < $item->users_count)){
        $exec .= 'gr.push({"id":"'.strval($item->id).'", "offset": '.intval($item->{$offset_key}).'}); ';
        $limit--;
      }
      // }else{
      //   $groups_in_cache[$item->id] = $cache_key;
      // }
    }

    if($limit > 0){
      foreach($groups as $item){
        $new_offet = intval($item->{$offset_key}) + $count;
        if($item->users_count && $item->users_count > $new_offet){
          while($item->users_count > $new_offet){
            $exec .= 'gr.push({"id":"'.strval($item->id).'", "offset": '.$new_offet.'});';
            $limit--;
            $new_offet += $count;
            if(!$limit){
              break 2;
            }
          }
        }
      }
    }


    // if((count($groups) > 20 && $limit < 10) || (count($groups) <= 20) && $limit < 25)
    // {
    $exec .= 'var i = 0;';
    $exec .= 'while(i < gr.length){';
    $exec .=  'result.push({"id":gr[i].id, "offset":gr[i].offset, "res": API.groups.getMembers({';
    $exec .=   '"group_id":gr[i].id,';
    $exec .=   '"count":'.$count.',';
    $exec .=   '"fields":"'.$fields.'",';
    $exec .=   '"offset":gr[i].offset})';
    $exec .=  '});';
    $exec .=  'i = i+1;';
    $exec .= '}';
    $exec .= 'return result;';
    
    
    $result = $this->VkApiController->vkr('execute', [
      "code" => $exec
    ]);

    $result = json_decode($result, true);
    // }else{
    //   $result['response'] = [];
    // }
    
    // Добавление в Кэш
    // foreach($result['response'] as $group){
    //   $cache_key = md5($count . $fields . strval($group['id']) . intval($group['offset']));
    //   $expiresAt = now()->addDays(60);
    //   Cache::put($cache_key, $group, $expiresAt);
    // }

    // foreach($groups_in_cache as $cache_key){
    //   $result['response'][] = Cache::get($cache_key);
    // }

    return $result['response'];
  }
  
  public function taskProssess(Request $request)
  {
    // Для автоматического продления сессии
    if(!empty(session('vk-token-expires_in')) && session('vk-token-expires_in') < strtotime('-2 hour') || empty(session('vk-token')))
    {
      session(['vk-token-expires_requestUri' => $request->requestUri]);
      return redirect()->route('vkapi.oauth');
    }

    //$groups_from_task = Modules\Task\Entities\Task::with(['vk_groups'])->where('id', $request->group_search_task_id)->first();
    $parse_users_task = Task::with(['task_data'])->where('id', $request->parse_users_task_id)->first(); //group_search_task_id
    //$parse_users_task->task_data->where('key', 'groups_search_task_id')->pluck('value')->first();

    if(in_array($parse_users_task->status, ["CREATED", "IN_PROGRESS"])){
      return $this->collectUserIdsOfGroup($parse_users_task);
    }else{
      return "Вроде все ок...";
    }
  }

  public function collectUserIdsOfGroup(Task $parse_users_task){
    $groups_search_task_id = $parse_users_task->task_data->where('key', 'groups_search_task_id')->pluck('value')->first();
    $groups_from_task = Task::find($groups_search_task_id);

    $groups = $groups_from_task->vk_groups()->where('users_count', NULL)->take(50)->get();
    if(!count($groups))
    {
      $groups = $groups_from_task->vk_groups()->whereColumn('users_collected',"<", "users_count")->take(50)->get();
    }

    //$result = $groups;
    if(!count($groups)){
      
      // Задание выполнено.
      $parse_users_task->status = "DONE";
      $parse_users_task->save();
      return redirect()->route('project.index')->with('success', "Задание <strong>".$parse_users_task->name."</strong> выполнено");
    }
    //users_count users_parsed

    $result = $this->API_groups_getMembers([
      'groups' => $groups,
      'count' => 1000,
      'limit' => 25,
      'fields' => '',
      'offset_key' => 'users_collected',
    ]);
    
    $user_to_files = 0;
    $user_to_sql = 0;
    $group_to_sql = 0;
    //dd($result);
    
    $fix_count = [];

    $groups_to_file = [];

    foreach($result as $group){
      $vkGroup = VkGroup::find($group['id']);

      // Fix
      // if(!$vkGroup->users_count){
      //   $file_size = $this->getPathToGroupUsersFile($group['id'], true);
      //   $fix_count[$group['id']] = $file_size;
      //   if($file_size !== false){
      //     $vkGroup->users_collected = $file_size;
      //     $vkGroup->users_count = $group['res']['count'];
      //     $vkGroup->save();
      //     continue;
      //   }
      // }
      

      if(empty($group['res'])){
        if(!$vkGroup->users_count){
          $vkGroup->users_count = 0;
          $vkGroup->users_parsed = 0;
          $vkGroup->users_collected = 0;
        }
      }elseif(empty($group['res']['items']) && !empty($group['res']['count'])){
        $vkGroup->users_count = $group['res']['count'];
      }else{
        $count_items = count($group['res']['items']);
        if($group['res']['count'] > 400){
          $str_to_file = implode("\n", $group['res']['items']);
          Storage::append($this->getPathToGroupUsersFile($group['id']), $str_to_file);
          $vkGroup->has_file = 1;
          $user_to_files += $count_items;
        }else{
          // Если не указан users_count, значит заполнения не было, значит пишем первую тысячу в базу
          $user_ids_to_group = [['vk_group_id' => $group['id'], 'vk_user_id' => $group['res']['items'][0]]];
          foreach($group['res']['items'] as $user){
            $user_ids_to_group[] = [$group['id'], $user];
          }
          DB::table('vk_group_vk_user')->insertOrIgnore($user_ids_to_group);
          $user_to_sql += $count_items;
          $group_to_sql += 1;
        // в другом случае пишем в файлы
        }
        // Обновляем данные по группе
        if(!$vkGroup->users_count){
          $vkGroup->users_count = $group['res']['count'];
        }
        if(!$vkGroup->users_collected){
          $vkGroup->users_collected = $count_items;
        }else{
          $vkGroup->users_collected += $count_items;
        }
      }
      $vkGroup->updated_at = now();
      $vkGroup->save();
    }
    //dd($fix_count);

    if($parse_users_task->status == "CREATED")
    {
      $parse_users_task->status = "IN_PROGRESS";
    }
    $parse_users_task->time += time(true) - $this->monitor['time_start'];
    $parse_users_task->save();

    echo "<pre>";
    echo date("Y-m-d H:i:s")."\r\n";
    echo "Кол-во групп: ".count($result)."\r\n";
    echo "Соединено пользователей с группами: ". ($user_to_files + $user_to_sql) ." (в SQL — ".$user_to_sql.", ".$group_to_sql." гр.) \r\n";
    echo "Секунд прошло: ". (time(true) - $this->monitor['time_start'])."\r\n";
    echo "</pre>";

    // Продолжить выполнение задания
    return redirect()->route('task.users-from-group.prossess', [
      'project_id' => request()->project_id,
      'parse_users_task_id' => $parse_users_task->id,
    ])->with('success', "Продолжаем выполнять задачу <strong>".$parse_users_task->name."</strong>");

  }
  public function getPathToGroupUsersFile($group_id, $return_count = false){
    // Разбиваем по маске, исходя из максимального кол-ва в 999 999 999
    $group_id_masked_split = str_split(sprintf("%09s", $group_id), 3);
    $filename = "groups/".implode("/", $group_id_masked_split).".txt";
    if(!$return_count){
      return $filename;
    }else{
      $direct_file_path = $_SERVER['DOCUMENT_ROOT'].'/../storage/app/'.$filename;
      if(file_exists($direct_file_path)){
        return sizeof(file($direct_file_path));
      }else{
        return false;
      }
    }
  }

  public function addUsersToDB(Task $groups_from_task, Task $parse_users_task){
    $this->monitor = [];
    $this->monitor['time_start'] = time(true);

    // Предустановленные переменные
    $today = date("j.n.Y");
    $deactivated = ['deleted' => 1, 'banned' => 2];

    /*
    Выбрать группы, где нет user_count
    Если таких нет, то выбрать группы, где user_count > users_parsed
    Так, для каждой группы загружать пользователей с offset, равным users_parsed
    */

    $groups = $groups_from_task->vk_groups->where('users_count', NULL)->take(50)->all();
    if(!count($groups))
    {
      $groups = $groups_from_task->vk_groups->filter(function($group){
        return $group->users_count > $group->users_parsed;
      })->take(50)->all();
    }
    if(!count($groups)){
      // Задание выполнено.
      $parse_users_task->status = "DONE";
      $parse_users_task->save();
      return redirect()->route('project.index')->with('success', "Задание <strong>".$parse_users_task->name."</strong> выполнено");
    }

    $result = $this->API_groups_getMembers([
      'groups' => $groups,
      'count' => 600,
      'limit' => 22,
      'fields' => 'sex,bdate,city,country,site,universities,can_see_all_posts,can_write_private_message,last_seen,status',
    ]);
    //dd($result);

    $users_inserted = VkUser::count();
    $users_linked_to_groups = DB::table('vk_group_vk_user')->count();

    foreach($result as $group){
      $vkGroup = VkGroup::find($group['id']);
      
      if(empty($group['res']['items'])){
        $vkGroup->is_closed = 1;
        $vkGroup->users_count = 99999999;
        $vkGroup->users_parsed = 99999999;
      }else{
        $users_to_insert = [];
        $user_ids_to_group = [];
        $city_ids = [];
        foreach($group['res']['items'] as $user)
        {
          $user_ids_to_group[] = ['vk_user_id' => $user['id'], 'vk_group_id' => $group['id'] ];
          $ta = [
            'id' => $user['id'],
            //'last_seen_days' => NULL,
            'last_seen' => NULL,
            //'first_name_id' => NULL,
            //'last_name_id' => NULL,
            'sex' => NULL,
            //'bdate_id' => NULL,
            'bdate' => NULL,
            'city_id' => NULL,
            'country_id' => NULL,
            'site' => NULL,
            'status' => NULL,
            'deactivated' => NULL,
            'can_access_closed' => NULL,
            'can_write_private_message' => NULL,
            //'univ_fast_string_id' => NULL,
            'univ_fast_string' => NULL,
            'parsed_date_id' => NULL,
          ];

          $ta['parsed_date_id'] = 1;

          if(isset($user['deactivated'])){
            $ta['deactivated'] = $deactivated[ $user['deactivated'] ];
          }else{
            if(isset($user['bdate']))
            {
              //$ta['bdate_id'] = array_search($user['bdate'], $vkUserBdates);
              $ta['bdate'] = $user['bdate'];
            }
            
            if(isset($user['universities']))
            {
              $fast_universities_string = [];
              foreach($user['universities'] as $u){
                $ta_u = [];
                if(isset($u['name']))
                {
                  $ta_u[] = $u['name'];
                }
                if(isset($u['faculty_name']))
                {
                  $ta_u[] = $u['faculty_name'];
                }
                if(isset($u['graduation']))
                {
                  $ta_u[] = '::'.$u['graduation'];
                }
                $fast_universities_string[] = implode("||", $ta_u);
              }
              $fast_universities_string = implode("----", $fast_universities_string);
              //$ta['univ_fast_string_id'] = array_search($fast_universities_string, $vkUserUnivFasts);
              $ta['univ_fast_string'] = $fast_universities_string;
            }

            $ta['sex'] = isset($user['sex']) ? $user['sex'] : NULL;
            $ta['can_access_closed'] = isset($user['can_access_closed']) ? $user['can_access_closed'] : NULL;
            $ta['can_write_private_message'] = isset($user['can_write_private_message']) ? $user['can_write_private_message'] : NULL;
            $ta['city_id'] = isset($user['city']) ? $user['city']['id'] : NULL;
            $ta['country_id'] = isset($user['country']) ? $user['country']['id'] : NULL;
            if(isset($user['last_seen']))
            {
              //$last_seen_time = new \DateTime(date("Y-m-d", $user['last_seen']['time']));
              //$now_time = new \DateTime($today);
              //$ta['last_seen_days'] = $last_seen_time->diff($now_time)->format('%a');
              $ta['last_seen'] = $user['last_seen']['time'];
            }

            $ta['site'] = isset($user['site']) ? $user['site'] : NULL;

            if(isset($user['status']))
            {
              foreach(['insta','инста'] as $status_interested){
                if(stripos($user['status'], $status_interested)){
                  $ta['status'] = $user['status'];
                  break;
                }
              }
            }
          }
          $users_to_insert[] = $ta;
        //break;
        }
        VkUser::insertOrIgnore($users_to_insert);
        DB::table('vk_group_vk_user')->insertOrIgnore($user_ids_to_group);

        $vkGroup->users_count = $group['res']['count'];
        if(!$vkGroup->users_parsed){
          $vkGroup->users_parsed = count($group['res']['items']);
        }else{
          $vkGroup->users_parsed += count($group['res']['items']);
        }
      }
      $vkGroup->updated_at = now();
      $vkGroup->save();
    }
    $VkUser_count = VkUser::count();
    echo "<pre>";
    echo date("Y-m-d H:i:s")."\r\n";
    echo "Кол-во групп: ".count($result)."\r\n";
    echo "Добавлено <strong>Users</strong>: ". ($VkUser_count - $users_inserted)." (Всего - ".number_format($VkUser_count, 0).")\r\n";
    echo "Соединено пользователей с группами: ". (DB::table('vk_group_vk_user')->count() - $users_linked_to_groups)."\r\n";
    echo "Секунд прошло: ". (time(true) - $this->monitor['time_start'])."\r\n";
    echo "</pre>";
    if($parse_users_task->status == "CREATED")
    {
      $parse_users_task->status = "IN_PROGRESS";
    }
    $parse_users_task->time += time(true) - $this->monitor['time_start'];
    $parse_users_task->save();

    // Продолжить выполнение задания
    return redirect()->route('task.users-from-group.prossess', [
      'project_id' => request()->project_id,
      'parse_users_task_id' => $parse_users_task->id,
      'group_search_task_id' => $groups_from_task->id,
    ])->with('success', "Продолжаем выполнять задачу <strong>".$parse_users_task->name."</strong>");
  }

}
