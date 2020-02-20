@extends('layouts.app')

@section('content')
<div class="container">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('project.index')}}">Проекты</a></li>
      <li class="breadcrumb-item active" aria-current="page">Добавить проект</li>
    </ol>
  </nav>

  <h1>Добавить проект</h1>
  {!! Form::open(['route' => ['project.store'], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
  <div class="form-group">
    <div class="form-row">
      <div class="col">
        {{Form::label('name','Название')}}
        {{Form::text('name', '', ['class' => 'form-control','placeholder' => 'Название'])}}
      </div>
    </div>
  </div>
  {{Form::submit('Сохранить', ['class' => 'btn btn-primary'])}}
  {!! Form::close() !!}
</div>
@endsection
