<?php
global $routes;
$routes = array();

$routes['/users/login']='/users/login'; //email e senha para loguin
$routes['/users/new']='/users/new_record';// name,email,pass obs:senha salva en forma de hash
$routes['/users/feed']='/users/feed/'; //info do user logado 
$routes['/users/{id}']='/users/view/:id';//info de user via id 
$routes['/users/{id}/photos']='/users/photos/:id';// info photo vai id
$routes['/users{id}/follow']='/users/follow/:id'; // info de seguindo ...e seguidores

$routes['/photos/random']='/photos/random';//photos aletoria
$routes['/photos/new']='/photos/new_record';// postar nova foto
$routes['/photos/{id}']='/photos/view/:id';// ver foto via id
$routes['/photos/{id}/comment']='/photos/comment/:id';// comentar na foto id
$routes['/photos/{id}/like']='/photos/like/:id';// like na foto id

$routes['/comment/{id}'] = '/photos/delete_comment/:id'; // deletar comentario 
?>