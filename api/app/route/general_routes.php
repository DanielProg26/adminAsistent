<?php

use App\Model\UserModel;
use App\Model\BDModel;

// Se instancia el modelo
$um = new UserModel();
$bdm = new BDModel();

$app->post('/login', function ($req, $res) {

    $params = $req->getParsedBody();

    $result =  $GLOBALS['um']->login($params);

    if(!isset($result)):
        return $this->response->withJson(array("error"=>"acceso negado"), 405);
    else:
        return $this->response->withJson($result, 200);
    endif;
});

$app->get('/logout', function ($req, $res, $args) {
    session_destroy();
    return $this->response->withJson(array("ok"=>"sesion finalizada"));
});

$app->get('/session', function ($req, $res, $args) {
    if($_SESSION['tipo'] !== 'Administrador'):
        return$this->response->withJson(array('msg'=>'acceso negado'));
    endif;
    return $this->response->withJson(array("usuario"=>$_SESSION['usuario'], "tipo"=>$_SESSION['tipo']));
});

$app->get('/app', function ($req, $res, $args) {

    $sql = 'SELECT * FROM app_config';

    $sth = $this->db->prepare($sql);

    $sth->execute();
    
    return $this->response->withJson($sth->fetch());

})->add($mw);

$app->group('/bd',function(){

    // FIXME: Probar ruta restaurar
    $this->post('/restore',function($req, $res){
        $params = $req->getParsedBody();
        $result = $GLOBALS['bdm']->restore($params['filename']);
        $this->logger->info("Restauracion de Base de datos");
        return $this->response->withJson($result);
    });

    $this->get('/backup',function($req, $res, $args){
        $result = $GLOBALS['bdm']->backup();
        $this->logger->info("Respaldo de Base de datos Ruta Archivo:".$result['filename']);
        return $this->response->withJson($result);
    });

    $this->post('/delete',function($req, $res){
        $params = $req->getParsedBody();
        $result = $GLOBALS['bdm']->delete($params['filename'], $params['id']);
        $this->logger->info("Archivo Borrado: ".$result['archivo']);

        return $this->response->withJson($result);
    });

    $this->get('/backups',function($req, $res, $args){        
        return $this->response->withJson($GLOBALS['bdm']->getBackups());
    });
})->add($mw);
