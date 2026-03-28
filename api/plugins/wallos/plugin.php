<?php
$GLOBALS['plugins']['Wallos'] = array(
    'name' => 'Wallos',
    'author' => 'JamesAdams',
    'category' => 'Subscriptions',
    'link' => 'https://github.com/JamesDAdams/organizrv2-plugin-wallos',
    'license' => 'personal',
    'idPrefix' => 'WALLOS',
    'configPrefix' => 'WALLOS',
    'version' => '1.0.0',
    'image' => 'plugins/images/wallos.png',
    'settings' => true,
    'bind' => true,
    'api' => 'api/v2/plugins/wallos/settings',
    'homepage' => true
);

class WallosPlugin extends Organizr
{
    public function __construct()
    {
        parent::__construct();
    }

    public function _pluginGetSettings()
    {
        return array(
            'Information' => array(
                    array(
                    'type' => 'html',
                    'label' => 'Description',
                    'html' => '<span lang="en">Configure your Wallos server integration to display your subscriptions on the Organizr homepage.</span> <span lang="en">The user need to have the same email between Organizr and Wallos.</span>'
                )
            ),
            'Wallos Settings' => array(
                    array(
                    'type' => 'select',
                    'name' => 'WALLOS-minAuth',
                    'label' => 'Minimum authentication to view component',
                    'value' => (string)($this->config['WALLOS-minAuth'] ?? '1'),
                    'options' => $this->groupSelect()
                ),
                    array(
                    'type' => 'input',
                    'name' => 'WALLOS-url',
                    'label' => 'Wallos URL',
                    'placeholder' => 'ex: https://wallos.domain.com',
                    'value' => (string)($this->config['WALLOS-url'] ?? '')
                ),
                    array(
                    'type' => 'password-alt',
                    'name' => 'WALLOS-apikey',
                    'label' => 'Wallos API Key',
                    'value' => (string)($this->config['WALLOS-apikey'] ?? '')
                ),
                    array(
                    'type' => 'input',
                    'name' => 'WALLOS-title',
                    'label' => 'Homepage component title',
                    'value' => (string)($this->config['WALLOS-title'] ?? 'Mes abonnements')
                )
            )
        );
    }
}
