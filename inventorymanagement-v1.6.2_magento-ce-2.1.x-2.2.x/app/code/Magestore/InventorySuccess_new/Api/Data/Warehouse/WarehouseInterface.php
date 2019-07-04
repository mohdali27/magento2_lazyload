<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Data\Warehouse;

interface WarehouseInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const WAREHOUSE_ID = 'warehouse_id';

    const WAREHOUSE_NAME = 'warehouse_name';

    const WAREHOUSE_CODE = 'warehouse_code';

    const CONTACT_EMAIL = 'contact_email';

    const TELEPHONE = 'telephone';

    const STREET = 'street';

    const CITY = 'city';

    const COUNTRY_ID = 'country_id';

    const REGION = 'region';

    const REGION_ID = 'region_id';

    const POSTCODE = 'postcode';

    const STATUS = 'status';

    const IS_PRIMARY = 'is_primary';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';
    
    const STORE_ID = 'store_id';    

    /**#@-*/
    
    
    const DEFAULT_WAREHOUSE_ID = 2;    

    /**
     * Warehouse id
     *
     * @return int|null
     */
    public function getWarehouseId();

    /**
     * Set warehouse id
     *
     * @param int|null $warehouseId
     * @return $this
     */
    public function setWarehouseId($warehouseId);

    /**
     * Warehouse name
     *
     * @return string
     */
    public function getWarehouseName();

    /**
     * Set warehouse name
     *
     * @param int $warehouseName
     * @return $this
     */
    public function setWarehouseName($warehouseName);

    /**
     * Warehouse code
     *
     * @return string
     */
    public function getWarehouseCode();

    /**
     * Set warehouse code
     *
     * @param int $warehouseCode
     * @return $this
     */
    public function setWarehouseCode($warehouseCode);

    /**
     * Contact email
     *
     * @return string|null
     */
    public function getContactEmail();

    /**
     * Set contact email
     *
     * @param int $contactEmail
     * @return $this
     */
    public function setContactEmail($contactEmail);

    /**
     * telephone
     *
     * @return int|null
     */
    public function getTelephone();

    /**
     * Set telephone
     *
     * @param int $telephone
     * @return $this
     */
    public function setTelephone($telephone);

    /**
     * Street
     *
     * @return string|null
     */
    public function getStreet();

    /**
     * Set street
     *
     * @param int $street
     * @return $this
     */
    public function setStreet($street);

    /**
     * City
     *
     * @return string|null
     */
    public function getCity();

    /**
     * Set city
     *
     * @param int $city
     * @return $this
     */
    public function setCity($city);

    /**
     * Country id
     *
     * @return string|null
     */
    public function getCountryId();

    /**
     * Set country id
     *
     * @param int $countryId
     * @return $this
     */
    public function setCountryId($countryId);

    /**
     * Region
     *
     * @return string|null
     */
    public function getRegion();

    /**
     * Set region
     *
     * @param int $region
     * @return $this
     */
    public function setRegion($region);

    /**
     * Region ID
     *
     * @return int|null
     */
    public function getRegionId();

    /**
     * Set region id
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId);

    /**
     * Postcode
     *
     * @return string|null
     */
    public function getPostcode();

    /**
     * Set postcode
     *
     * @param int $postcode
     * @return $this
     */
    public function setPostcode($postcode);

    /**
     * Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Is primary
     *
     * @return boolean
     */
    public function getIsPrimary();

    /**
     * Set is primary
     *
     * @param int $isPrimary
     * @return $this
     */
    public function setIsPrimary($isPrimary);

    /**
     * Created at
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @param int $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Updated at
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated at
     *
     * @param int $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * get Store Id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);    
}