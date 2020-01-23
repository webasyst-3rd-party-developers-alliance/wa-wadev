<?php

/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version
 * @copyright Serge Rodovnichenko, 2017
 * @license
 */
class wadevSettingsSaveController extends waJsonController
{
    protected $valid_settings = ['api_key', 'refresh_rate'];

    public function execute()
    {
        $this->clearInfo();

        $data = (array)$this->getRequest()->post('data', array(), waRequest::TYPE_ARRAY);
        $AppSetting = new wadevSettingsModel();

        foreach ($data as $key => $val) {
            if (in_array($key, $this->valid_settings) && !empty($val)) {
                $AppSetting->set(wa()->getUser()->getId(), $key, $val);
            }
        }
    }

    protected function clearInfo()
    {
        $model_classes = array(
            'transactions' => 'wadevTransactionModel',
            'promocodes'   => ['wadevPromocodeModel', 'wadevPromocodeProductsModel'],
            'products'     => ['wadevProductModel', 'wadevPromocodeProductsModel']
        );

        $options = (array)$this->getRequest()->post('reset', [], waRequest::TYPE_ARRAY);

        if (!is_array($options) || empty($options)) {
            return;
        }

        foreach ($options as $key => $val) {
            if (!$val || !isset($model_classes[$key])) {
                continue;
            }

            foreach ((array)$model_classes[$key] as $model) {
                if (class_exists($model)) {
                    (new $model)->deleteByContactId();
                }
            }

            if ($key == 'transactions') {
                (new wadevSettingsModel())->del('api.transactions');
                (new wadevSettingsModel())->del('new_transactions');
            }
        }
    }
}
