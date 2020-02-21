<?php

namespace Modules\Project\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use Modules\Project\Entities\Project;

use Modules\Task\Entities\Task;

class ProjectController extends Controller
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
        $projects = Project::all();
        return view('project::index')->with('projects', $projects);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('project::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $project = new Project;
        $project->name = $request->name;
        $project->user_id = auth()->user()->id;
        $project->save();

        return redirect()->route('project.index')->with('success', "Проект <strong>".$request->name."</strong> создан");
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $project = Project::find($id);
        if($project->user_id != auth()->user()->id)
        {
            return redirect()->route('project.index')->with('error', "<strong>Ой!</strong> Доступ ограничен");
        }
        $tasks = Task::where('project_id', $id)->get();
        return view('project::show', ['project' => $project, 'tasks' => $tasks]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $project = Project::find($id);
        if($project->user_id != auth()->user()->id)
        {
            return redirect()->route('project.index')->with('error', "<strong>Ой!</strong> Доступ ограничен");
        }
        return view('project::edit')->with('project', $project);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $project = Project::find($id);

        if($project->user_id != auth()->user()->id)
        {
            return redirect()->route('project.index')->with('error', "<strong>Ой!</strong> Доступ ограничен");
        }


        $project->name = $request->name;
        $project->save();

        return redirect()->route('project.index')->with('success', "Проект <strong>".$request->name."</strong> обновлен");
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $project = Project::find($id);

        if($project->user_id != auth()->user()->id)
        {
            return redirect()->route('project.index')->with('error', "<strong>Ой!</strong> Доступ ограничен");
        }
        $project_name = $project->name;
        $project->delete();
        return redirect()->route('project.index')->with('success', "Проект <strong>".$project_name."</strong> удален");
    }
}
