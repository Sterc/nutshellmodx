<?php
/**
 * NutshellModx Connector
 *
 * @package nutshellmodx
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('nutshellmodx.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/nutshellmodx/');
$nutshellmodx = $modx->getService(
    'nutshellmodx',
    'NutshellModx',
    $corePath . 'model/nutshellmodx/',
    array(
        'core_path' => $corePath
    )
);

/* handle request */
$modx->request->handleRequest(
    array(
        'processors_path' => $nutshellmodx->getOption('processorsPath', null, $corePath . 'processors/'),
        'location' => '',
    )
);