<?php

namespace Sipay\Sipay\Controller\Backend;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

if (interface_exists("Magento\Framework\App\CsrfAwareActionInterface"))
    include __DIR__ . "/Index.m23.php";
else
    include __DIR__ . "/Index.m22.php";
