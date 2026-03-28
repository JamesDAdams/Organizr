<?php

trait WallosHomepageItem
{
    public function wallosSettingsArray($infoOnly = false)
    {
        $homepageInformation = [
            'name' => 'Wallos',
            'enabled' => true,
            'image' => 'plugins/images/wallos.png',
            'category' => 'Subscriptions',
            'settingsArray' => __FUNCTION__
        ];
        if ($infoOnly) {
            return $homepageInformation;
        }
        $homepageSettings = [
            'debug' => true,
            'settings' => [
                'Enable' => [
                    $this->settingsOption('enable', 'homepageWallosEnabled', ['label' => 'Activate Wallos', 'help' => 'Display the Wallos module on the home page']),
                    $this->settingsOption('auth', 'homepageWallosAuth', ['label' => 'Authentification']),
                ],
            ]
        ];
        return array_merge($homepageInformation, $homepageSettings);
    }

    public function wallosHomepagePermissions($key = null)
    {
        $permissions = [
            'main' => [
                'enabled' => [
                    'homepageWallosEnabled'
                ],
                'auth' => [
                    'homepageWallosAuth'
                ]
            ]
        ];
        return $this->homepageCheckKeyPermissions($key, $permissions);
    }

    public function homepageOrderWallos()
    {
        if ($this->homepageItemPermissions($this->wallosHomepagePermissions('main'))) {

            return '
				<div id="' . __FUNCTION__ . '">
                    <div style="display: flex; align-items: center; margin-bottom: 20px; font-family: \'Open Sans\', \'Segoe UI\', sans-serif; padding-left: 5px; margin-top: 20px;">
                        <div style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; margin-right: 14px;">
                            <img src="plugins/images/wallos.png"
                                 style="width: 26px; height: 26px; object-fit: contain;" alt="Wallos Logo">
                        </div>
                        <span style="color: #eee; font-size: 19px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;">' . ($this->config['WALLOS-title'] ?? 'My Subscriptions') . '</span>
                    </div>

                    <style>
                        @keyframes pulse {
                            0% { background-color: #2d2d2d; }
                            50% { background-color: #3d3d3d; }
                            100% { background-color: #2d2d2d; }
                        }
                        .skeleton-card {
                            width: 150px;
                        }
                        .skeleton-poster {
                            width: 100%;
                            aspect-ratio: 2 / 3;
                            background: #2d2d2d;
                            border-radius: 4px;
                            animation: pulse 1.5s infinite ease-in-out;
                            margin-bottom: 8px;
                        }
                        .skeleton-text {
                            height: 12px;
                            background: #2d2d2d;
                            border-radius: 2px;
                            animation: pulse 1.5s infinite ease-in-out;
                            margin-bottom: 6px;
                        }
                    </style>

                    <div id="wallos-subscriptions-container" style="display: flex; flex-wrap: wrap; gap: 18px; margin-bottom: 50px; padding-left: 5px;">
                        <div class="skeleton-card"><div class="skeleton-poster"></div><div class="skeleton-text" style="width: 80%;"></div><div class="skeleton-text" style="width: 50%;"></div></div>
                        <div class="skeleton-card"><div class="skeleton-poster"></div><div class="skeleton-text" style="width: 70%;"></div><div class="skeleton-text" style="width: 40%;"></div></div>
                        <div class="skeleton-card"><div class="skeleton-poster"></div><div class="skeleton-text" style="width: 85%;"></div><div class="skeleton-text" style="width: 60%;"></div></div>
                        <div class="skeleton-card"><div class="skeleton-poster"></div><div class="skeleton-text" style="width: 75%;"></div><div class="skeleton-text" style="width: 45%;"></div></div>
                    </div>
				</div>
				';
        }
    }

}
