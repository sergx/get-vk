<?php

namespace Modules\VkApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class VkApiController extends Controller
{

  private $access_token;
  private $api_version = "5.103";
  private $url = 'https://api.vk.com/method/';

  private $groups_added = 0;

  // Конструктор
  public function __construct()
  {
    // Конструкция для того чтобы получить доступ к сессии и пользователю из конструктора
    $this->middleware(function ($request, $next)
    {
      $this->access_token = session('vk-token');
      return $next($request);
    });
  }

  /**
   * Делает запрос к API VK
   * @param $method
   * @param $params
   */
  public function vkr($method, $params = null)
  {
    $p = '';

    if ($params && is_array($params))
    {
      foreach ($params as $key => $param)
      {
        $p .= ($p == '' ? '' : '&') . $key . '=' . urlencode($param);
      }
    }

    $curl_handle = curl_init();
    $requrest_url = $this->url . $method . '?' . ($p ? $p . '&' : '') . 'access_token=' . $this->access_token . '&v=' . $this->api_version;
    
    // Попытка взять из кэша
    $cache_key = 'vk_'.md5($requrest_url);
    if (Cache::has($cache_key)) {
      return Cache::get($cache_key);
    }

    curl_setopt($curl_handle, CURLOPT_URL, $requrest_url);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($curl_handle);

    $result_code = curl_getinfo($curl_handle, CURLINFO_RESPONSE_CODE);
    $result_length = curl_getinfo($curl_handle, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

    curl_close($curl_handle);

    if ($response)
    {
      // запись в кэш
      Cache::forever($cache_key, $response);

      // Запись в "лог"
      $VkRequestHistory = new VkRequestHistory;
      $VkRequestHistory->url = $requrest_url;
      $VkRequestHistory->method = $method;
      $VkRequestHistory->cache_key = $cache_key;
      $VkRequestHistory->params = json_encode($params, 256);
      $VkRequestHistory->result_code = $result_code;
      $VkRequestHistory->result_length = $result_length;
      $VkRequestHistory->save();

      return $response;
    }

    return false;
  }

  public function vkRequestHandler(Request $request, $method)
  {
    switch($method)
    {
      case "groups.search":
        //
        $params = [
          "q" => $request->q,
          "count" => 1000,
          "offset" => 0,
        ];
        return $this->addGroupsToDB($params);
      break;
    }
  }

  public function addGroupsToDB($params){
    // {"q":"Сварка","count":250,"offset":0}

    $code = 'var result = [];'."\n";
    foreach(['group', 'page'] as $type)
    {
      foreach([0,1,2,3,4,5] as $sort_order)
      {
        $code .= 'result.push({"sort_type":'.$sort_order.', "res":API.groups.search({"q":"'.$params['q'].'","count":1000,"offset":0,"type":"'.$type.'","sort":'.$sort_order.'})});'."\n";
      }
    }
    $code .= 'return result;';
    //return "<pre>".$code."</pre>";
    $result = json_decode($this->vkr('execute', [
      "code" => sprintf($code, json_encode($params, 256))
    ]), true);

    $need_more = false;
    //print_r($result['error']);
    //return;
    if(!empty($result['error'])){
      return redirect()->route('vk-index')->with("error", '<strong>Ошибка</strong><pre>'.print_r($result['error'], true).'</pre>');
    }
    foreach($result['response'] as $chunk){
      $sort_type = $chunk['sort_type'];
      $data_to_insert = [];
      foreach($chunk['res']['items'] as $item){             
        $validator = Validator::make(
          [
            'id' => $item['id']
          ],
          [
            'id' => 'unique:vk_groups'
          ]);
        
        if($validator->passes()){
          $data_to_insert[] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'screen_name' => $item['screen_name'],
            'type' => $item['type'],
            'sort_type' => $sort_type,
            'is_closed' => $item['is_closed'],
            'is_closed' => $item['is_closed'],
          ];
          $this->groups_added++;
        }
      }
      VkGroup::insert($data_to_insert);
    }
    if($need_more){
      usleep(350000);
      return $this->addGroupsToDB(array_merge($params, ['offset' => $need_more]));
    }else{
      return redirect()->route('vk-index')->with("success", "Все ок! Добавлено групп ".$this->groups_added);
    }
  }


  public function index()
  {
    //session()->forget('vk-token', 'vk-token-expires_in');
    return view("parse.vk.index");
  }

  // отсылается POST запрос с полученым кокеном
  public function tokenSave(Request $request){
    // Создать таблицу, в которой хранится токен и указано время expire
    $url = parse_url($request->input('vk-token-url'));
    $params = [];
    foreach(explode("&", str_replace('#', '', $url["fragment"])) as $parametr){
      $parametr = explode("=", $parametr);
      $params[$parametr[0]] = $parametr[1];
    }
    session(['vk-token' => $params['access_token']]);
    session(['vk-token-expires_in' => time() + $params['expires_in']]);
    return redirect()->route('vk-index');
  }
}


/*
    $code = '
    var params = %s;
    //var params = {"q":"Сварка","count":250,"offset":0};
    var chunk_size = params.count;
    var result = [];
    var test1 = [];
    result.push(API.groups.search(params));
    var items_count = result[0]["items"].length;

    if(result[0].count > chunk_size){
      var elems_left = result[0].count - chunk_size;
      var call_id = 1;
      while(elems_left > 0){
        if(call_id > 24){
          result.push({"need_more": (params.offset + chunk_size)});
          return result;
        }
        if(items_count >= 1000){
          result.push({"need_more": (params.offset + chunk_size)});
          return result;
        }
        params.offset = params.offset + chunk_size;
        result.push(API.groups.search(params));
        items_count = items_count + result[call_id]["items"].length;
        call_id = call_id + 1;
        elems_left = elems_left - chunk_size;
      }
    }
    return result;
    ';
*/
