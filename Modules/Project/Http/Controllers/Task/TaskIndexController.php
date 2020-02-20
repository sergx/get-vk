<?php

namespace Modules\Project\Http\Controllers\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use Modules\Project\Entities\Project;

class TaskIndexController extends Controller
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
        $tasks = Task::all();
        return view('project::task-index.index')->with('tasks', $tasks);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(Request $request)
    {
        $task_routs = [
            (object)[
                'name' => 'GroupsSearch (Поиск групп)',
                'route' => route('task.groups-search.create', $request->project_id),
            ],
        ];

        $project = Project::find($request->project_id);

        return view('project::task-index.create', ['task_routs' => $task_routs, 'project' => $project]);
    }
}
