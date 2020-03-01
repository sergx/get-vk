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
  <h1>{{$task->name}}</h1>
  <ul>
    <li>Статус: {{$task->status}}</li>
    <li>Потрачено времени: {{ $task->time / 60 }} минут</li>
    @if ($task->status !== "DONE")
    <li><a href="{{route('task.users-from-group.prossess', ['project_id' => $task->project->id, 'parse_users_task_id' => $task->id])}}">Продолжить парсинг</a></li>
    @endif
    <li>Пользоватьских ID собрано: {{ number_format($stat_data['user_ids_collected'], 0)}}</li>
    <li>Групп всего: {{$stat_data['total_groups_count']}}</li>
    <li>Группы, у которых не удалось получить IDs: {{$stat_data['total_groups_count'] - $stat_data['filled_groups_count']}}</li>
    <li>Закрытые группы: {{$stat_data['closed_groups_count']}}</li>
    <li>Закрытые группы, у которых удалось получить IDs: {{$stat_data['closed_groups_count_with_open_ids']}}</li>
  </ul>
</div>
@endsection
