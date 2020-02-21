<?php

namespace Modules\Task\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use Modules\Project\Entities\Project;

use Modules\Task\Entities\Task;
use Modules\Task\Entities\TaskData;

class TaskGroupsSearchController extends Controller
{
    public $controller_data = [
        ''
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth'/*, ['except' => ['index','show']] */);
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
        $this->validate($request, [
            'search_query' => 'required',
        ]);
        
        $search_types = [
            0 => 'По-умолчанию',
            1 => 'Макс.',
        ];

        $task = new Task;
        $task->name = $request->name?: "Поиск по группам — «".$request->search_query."», ".$search_types[intval($request->group_search_type)].", ".( intval($request->limit) );
        $task->project_id = $request->project_id;
        $task->task_key = "groups-search";
        $task->save();
        
        foreach(['search_query','group_search_type','limit']
            as $key){
            $td = new TaskData;
            $td->task_id = $task->id;
            $td->key = $key;
            $td->value = $request->{$key};
            $td->save();
        }

        return redirect()->route('project.show', $request->project_id)->with('success', "Проект <strong>".$request->name."</strong> создан");
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show(Request $request)
    {
        $task = Task::with(['project','task_data'])->where('id',$request->task_id)->first();
        $task_data = []; // search_query, group_search_type, limit
        foreach($task->task_data as $td){
            $task_data[$td->key] = $td->value;
        }
        if($task->project->user_id != auth()->user()->id)
        {
            return redirect()->route('task.index')->with('error', "<strong>Ой!</strong> Доступ ограничен");
        }

        return view('task::groups-search.show', ['task' => $task, 'task_data' => $task_data]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        // $task = Task::find($id);
        // if($task->user_id != auth()->user()->id)
        // {
        //     return redirect()->route('task.index')->with('error', "<strong>Ой!</strong> Доступ ограничен");
        // }
        // return view('task::groups-search.edit')->with('task', $task);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        // $this->validate($request, [
        //     'name' => 'required',
        // ]);

        // $task = Task::find($id);

        // if($task->user_id != auth()->user()->id)
        // {
        //     return redirect()->route('task.index')->with('error', "<strong>Ой!</strong> Доступ ограничен");
        // }


        // $task->name = $request->name;
        // $task->save();

        // return redirect()->route('task.index')->with('success', "Проект <strong>".$request->name."</strong> обновлен");
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        // $task = Task::find($id);

        // if($task->user_id != auth()->user()->id)
        // {
        //     return redirect()->route('task.index')->with('error', "<strong>Ой!</strong> Доступ ограничен");
        // }
        // $task_name = $task->name;
        // $task->delete();
        // return redirect()->route('task.index')->with('success', "Проект <strong>".$task_name."</strong> удален");
    }
}
