<?php
class generalService extends OService{
  function __construct(){
    $this->loadService();
  }
  
  public function getOpened($date){
    $db = new ODB();
    $sql = "SELECT * FROM `caja` WHERE DATE(`apertura`) = ?";
    $db->query($sql, [$date]);
    
    if ($res = $db->next()){
      return true;
    }
    else{
      return false;
    }
  }
  
  public function getAppData(){
    $app_data_file = $this->getConfig()->getDir('app_cache').'app_data.json';
    if (file_exists($app_data_file)){
      return file_get_contents($app_data_file);
    }
    else{
      return 'null';
    }
  }
  
  public function saveAppData($tipo_iva, $iva_list, $margin_list, $venta_online, $fecha_cad){
    $app_data_file = $this->getConfig()->getDir('app_cache').'app_data.json';
    
    $data = [
      'tipoIva'     => $tipo_iva,
      'ivaList'     => $iva_list,
      'marginList'  => $margin_list,
      'ventaOnline' => $venta_online,
      'fechaCad'    => $fecha_cad
    ];
    
    $data_str = json_encode($data);
    
    file_put_contents($app_data_file, $data_str);
  }
}