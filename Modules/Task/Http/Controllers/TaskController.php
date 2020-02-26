<?php

namespace Modules\Task\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use Modules\Project\Entities\Project;
use Modules\Task\Entities\Task;
use Modules\Task\Entities\TaskData;

class TaskController extends Controller
{

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
        // $tasks = Task::all();
        // return view('task::index')->with('tasks', $tasks);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(Request $request)
    {
        // $task_routs = [
        //     (object)[
        //         'name' => 'GroupsSearch (Поиск групп)',
        //         'route' => route('task.groups-search.create', $request->project_id),
        //     ],
        //     (object)[
        //         'name' => 'UsersFromGroup (Парсинг пользователей из групп)',
        //         'route' => route('task.users-from-group.create', $request->project_id),
        //     ],
        // ];

        // $project = Project::find($request->project_id);

        // return view('task::create', ['task_routs' => $task_routs, 'project' => $project]);
    }

    public function destroy(Request $request)
    {
        $project = Project::find($request->project_id);
  
        if($project->user_id != auth()->user()->id)
        {
            return redirect()->route('project.index')->with('error', "<strong>Ой!</strong> Доступ ограничен");
        }
  
        $task = Task::find($request->task_id);
        $task_name = $task->name;
        $task->delete();
        
        return redirect()->route('project.index')->with('success', "Задача <strong>".$task_name."</strong> удалена");
    }
}
