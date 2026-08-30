<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'SGA API',
    description: 'API del Sistema de Gestión Académica para centros infantiles'
)]
#[OA\PathItem(path: '/')]
class OpenApi
{
    //
}
