<?php

class CountrysController extends AppController {

    // Lấy đầu số quốc gia
    function getCountry() {
        APP::import("Model", array("Country", "DetailCountry"));
        $this->Country = new Country();
        $this->Country->contain("DetailCountry");
        $data = $this->Country->find("all");
       $result = $dataSections = $dataCell = array();
        foreach ($data as $val) {
            $dataSections[] = $val["Country"]["name"];
            $list = array();
            foreach ($val["DetailCountry"] as $value) {
                $list[] = array("key" => $value["key_country"], "value" => $value["name_country"], "country_id" => $value["id"]);
            }
            $dataCell[] = $list;
        }
        foreach ($dataCell as $val)
        {
            foreach ($val as $value)
                $result[]= $value;
        }
        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array("dataSection" => $dataSections, "dataCell" => $dataCell,"result" => $result)));
    }

}
