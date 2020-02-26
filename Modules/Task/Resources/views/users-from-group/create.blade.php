@extends('layouts.app')

@section('content')
<div class="container">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('project.index')}}">Проекты</a></li>
      <li class="breadcrumb-item"><a href="{{route('project.show', $project->id)}}">{{$project->name}}</a></li>
      <li class="breadcrumb-item active" aria-current="page">Добавить задание</li>
    </ol>
  </nav>
  <h1>Добавить задание</h1>
  
  {!! Form::open(['route' => ['task.users-from-group.store', $project->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
  {{Form::hidden('project_id', $project->id)}}

  <div class="form-group">
    <div class="form-row">
      <div class="col">
        {{Form::label('group_search_task_id','Какие группы парсить')}}
        <select class="form-control" id="group_search_task_id" name="group_search_task_id">
          @foreach ($group_search_tasks as $task)
            <option value="{{$task->id}}">Результат задания «{{$task->name}}» — {{ $task_data->{$task->id}->group_count ?? 'N/A'}} групп(ы)</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>

  {{Form::submit('Собрать', ['class' => 'btn btn-primary'])}}
  {!! Form::close() !!}

<pre>
Разные типы задания - разные контроллеры?
Уж точно разные обработчики
</pre>
</div>
@endsection
