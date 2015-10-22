<?php
    $service_names = $dsp->services->GetAll();
    
    $service_names_text = array();
    
    foreach ( $service_names AS $_service )
    {
        define(strtoupper($_service['name']) . '_SERVICE_ID', (int)$_service['id']);
    }
?>