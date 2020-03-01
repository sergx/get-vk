@extends('layouts.app')

@section('content')
<div class="container">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('project.index')}}">Проекты</a></li>
      <li class="breadcrumb-item"><a href="{{route('project.show', $task->project->id)}}">{{$task->project->name}}</a></li>
      <li class="breadcrumb-item active" aria-current="page">Задание</li>
    </ol>
  </nav>
  <h1>Задание</h1>

  <p><strong>Входные данные:</strong></p>
  <ul>
    <li><strong>Поисковый запрос</strong> <span>{{$task_data['search_query']}}</span></li>
    <li><strong>Тип поиска</strong> <span>{{ intval($task_data['group_search_type']) }}</span></li>
    <li><strong>Лимит</strong> <span>{{ intval($task_data['limit']) }}</span></li>
  </ul>

  <p><strong>Результат:</strong></p>
  <ul>
    <li><strong>Получено групп</strong> <span>{{count($task->vk_groups)}}</span></li>
  </ul>
  
  {!! Form::open(['route' => ['task.users-from-group.store', $task->project->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
  {{Form::hidden('project_id', $task->project->id)}}
  {{Form::hidden('group_search_task_id', $task->id)}}
  {{Form::submit('Собрать', ['class' => 'btn btn-primary'])}}
  {!! Form::close() !!}

  <table>
    <tbody>
      @foreach ($task->vk_groups as $item)
        <tr>
          <td>{{$item->id}}</td>
          <td>{{$item->name}}</td>
          <td><a href="https://vk.com/{{$item->screen_name}}" target="_blank">{{$item->screen_name}}</a></td>
          <td style="white-space:nowrap;">{{$item->users_count}} / {{$item->users_collected}} / {{$item->users_parsed}}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
