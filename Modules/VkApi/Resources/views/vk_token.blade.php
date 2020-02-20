@if(session('vk-token') && time() < session('vk-token-expires_in'))
<div class="alert alert-success">
  Токен есть. Истекает через {{gmdate("d д. H:i:s", session('vk-token-expires_in') - time())}}
</div>
@else
<div class="alert alert-warning">
Токена нет
</div>
{!!Form::open(['action' => 'VkApiController@tokenSave', 'method' => 'POST', 'class' => 'pull-right', 'id' => 'token-save'])!!}
{{Form::hidden('vk-token-url','',['id' => 'token-save-input'])}}
<a href="https://oauth.vk.com/authorize?client_id=7314679&display=page&redirect_uri={{route('vk-index')}}&scope=photos,groups&response_type=token&v=5.103" class="btn btn-primary">Получить токен</a>
{{Form::submit('Сохранить токен',['class' => 'btn btn-primary'])}}
{!!Form::close()!!}
<script>
var url = window.location.href;
$("#token-save-input").val(url);
</script>
@endif
