<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Magestore\InventorySuccess\Test\Unit\Model\AdjustStock;

use Magestore\InventorySuccess\Model\AdjustStock\AdjustStockManagement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class AdjustStockManagementTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->adjustStockManagement = $this->objectManagerHelper
                                            ->getObject('Magestore\InventorySuccess\Model\AdjustStock\AdjustStockManagement');
        
    }
    
    public function testGenerateCode()
    {
        $code = $this->adjustStockManagement->generateCode();
        $this->assertEquals('code123', $code);
    }
}