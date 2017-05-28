<?php
require 'config.php';
require 'vendor/autoload.php';

function print_nice($ref, $level = 0)
{
    $level++;
    $return = '';
    if (is_object($ref)) {
        $vars = get_object_vars($ref);
        $return .= print_nice($vars, $level);
    } elseif (is_array($ref)) {
        foreach ($ref as $r) {
            $return .= print_nice($r, $level);
        }
    } else {
        $return .= str_repeat('-', $level) . $ref . '<br />';
    }
    return $return;
}

try {
    $client = new GuzzleHttp\Client(['base_uri' => 'https://management.azure.com/']);
    $res = $client->request('POST', 'https://login.microsoftonline.com/funds.sa.gov.au/oauth2/token', [
    'form_params' => [
        'grant_type' => 'client_credentials',
        'client_id' => $clientID,
        'client_secret' => $clientSecret,
        'resource' => 'https://management.azure.com/'
    ]
    ]);

    $json = json_decode($res->getBody());

    $subscriptionId = 'a0a4759a-3c1d-4eb6-bf3f-1ae51a377cdc';
    $resourceGroup = 'Matrix';

    $apiVersion = '2016-04-30-preview';



    $url1 = "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/virtualmachines?api-version={$apiVersion}";

    $request1 = $client->request('GET', $url1, [
    'headers' => [
        'Authorization' => 'Bearer '. $json->access_token,
        'Content-Type' => 'application/json',
        'host' => 'management.azure.com'
    ]
    ]);
    $vms = json_decode($request1->getBody());

    foreach ($vms->value as $vm) {
        //$vm = $temp->properties;
        echo '<pre>';
        //print_r($vm);
        echo "+ {$vm->name} {$vm->location} ({$vm->id}) <br/>";
        $request2 = $client->request('GET', $vm->id . "/InstanceView?api-version={$apiVersion}", [
            'headers' => [
                'Authorization' => 'Bearer '. $json->access_token,
                'Content-Type' => 'application/json',
                'host' => 'management.azure.com'
            ]
        ]);
        $vmInfo = json_decode($request2->getBody());
        print_r($vmInfo);
    }

    $url2 = "/subscriptions/{subscriptionId}/resourceGroups/{resourceGroup}/providers/Microsoft.Compute/virtualMachines/{vm}/InstanceView?api-version={$apiVersion}";

    exit();
    $command = 'start';
    $command = 'powerOff';
    $command = 'deallocate';
    $vm = 'MatrixApp';
    $url = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vm}/{$command}?api-version={$apiVersion}";


    $request = $client->request('POST', $url, [
    'headers' => [
        'Authorization' => 'Bearer '. $json->access_token,
        'Content-Type' => 'application/json',
        'host' => 'management.azure.com'
    ]
    ]);

    echo $request->getStatusCode();
} catch (GuzzleHttp\Exception\ClientException $e) {
    echo $e->getMessage();
    echo $e->getResponse()->getBody()->getContents();
}
