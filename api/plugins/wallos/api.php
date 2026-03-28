<?php
$app->get('/plugins/wallos/subscriptions', function ($request, $response, $args) {
    $wallosPlugin = new WallosPlugin();
    $GLOBALS['api']['response']['data'] = [];
    $logger = $wallosPlugin->log('Wallos Plugin');

    if ($wallosPlugin->checkRoute($request)) {
        if ($wallosPlugin->qualifyRequest($wallosPlugin->config['WALLOS-minAuth'], true)) {
            $url = $wallosPlugin->config['WALLOS-url'] ?? '';
            $apiKey = $wallosPlugin->config['WALLOS-apikey'] ?? '';

            $memberEmail = $wallosPlugin->user['email'] ?? '';
            $logger->info('Wallos subscriptions request', [
                'user' => $wallosPlugin->user['username'] ?? '',
                'email' => $memberEmail,
                'url' => $url ? rtrim($url, '/') : ''
            ]);

            if ($url && $apiKey) {
                $endpoint = '/api/subscriptions/get_subscriptions.php?apiKey=' . $apiKey . '&image_base64=1';
                if ($memberEmail !== '') {
                    $endpoint .= '&member_email=' . rawurlencode($memberEmail);
                }

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, rtrim($url, '/') . $endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $res = curl_exec($ch);
                curl_close($ch);

                $logger->info('Wallos response received', [
                    'responseSize' => is_string($res) ? strlen($res) : 0,
                    'responseSnippet' => is_string($res) ? substr($res, 0, 500) : ''
                ]);

                if ($res) {
                    $raw_data = json_decode($res, true);
                    $data = is_array($raw_data) && isset($raw_data[0]) ? $raw_data[0] : $raw_data;

                    $logger->info('Wallos response decoded', [
                        'jsonError' => json_last_error_msg(),
                        'success' => $data['success'] ?? null,
                        'hasSubscriptions' => isset($data['subscriptions'])
                    ]);

                    if (isset($data['success']) && $data['success'] && isset($data['subscriptions'])) {
                        $filtered_subscriptions = $data['subscriptions'];
                        $logger->info('Wallos subscriptions payload', [
                            'userEmail' => strtolower(trim($memberEmail)),
                            'subscriptionsCount' => is_array($data['subscriptions']) ? count($data['subscriptions']) : 0
                        ]);
                        $GLOBALS['api']['response']['data'] = [
                            'title' => $wallosPlugin->config['WALLOS-title'] ?? 'My Subscriptions',
                            'subscriptions' => $filtered_subscriptions,
                            'url' => rtrim($url, '/')
                        ];
                    }
                }
            }
        }
    }

    $response->getBody()->write(jsonE($GLOBALS['api']));
    return $response
    ->withHeader('Content-Type', 'application/json;charset=UTF-8')
    ->withStatus($GLOBALS['responseCode']);
});

$app->get('/plugins/wallos/settings', function ($request, $response, $args) {
    $wallosPlugin = new WallosPlugin();
    if ($wallosPlugin->checkRoute($request)) {
        if ($wallosPlugin->qualifyRequest(1, true)) {
            $GLOBALS['api']['response']['data'] = $wallosPlugin->_pluginGetSettings();
        }
    }
    $response->getBody()->write(jsonE($GLOBALS['api']));
    return $response
    ->withHeader('Content-Type', 'application/json;charset=UTF-8')
    ->withStatus($GLOBALS['responseCode']);
});

$app->get('/plugins/wallos/logo', function ($request, $response, $args) {
    $wallosPlugin = new WallosPlugin();
    $logger = $wallosPlugin->log('Wallos Plugin');
    if (!$wallosPlugin->checkRoute($request)) {
        return $response->withStatus(403);
    }
    if (!$wallosPlugin->qualifyRequest($wallosPlugin->config['WALLOS-minAuth'], true)) {
        return $response->withStatus(403);
    }

    $url = $wallosPlugin->config['WALLOS-url'] ?? '';
    $params = $request->getQueryParams();
    $file = $params['file'] ?? '';
    $file = basename($file);

    if (!$url || $file === '') {
        return $response->withStatus(404);
    }

    $logoUrl = rtrim($url, '/') . '/images/uploads/logos/' . rawurlencode($file);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $logoUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $body = curl_exec($ch);
    $curlError = curl_error($ch);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    $logger->info('Wallos logo fetch', [
        'file' => $file,
        'url' => $logoUrl,
        'status' => $statusCode,
        'contentType' => $contentType,
        'responseSize' => is_string($body) ? strlen($body) : 0,
        'curlError' => $curlError
    ]);

    if (!$body || $statusCode < 200 || $statusCode >= 300) {
        $fallbackPath = $wallosPlugin->root . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'wallos.png';
        if (is_file($fallbackPath)) {
            $fallbackBody = file_get_contents($fallbackPath);
            if ($fallbackBody !== false) {
                $response->getBody()->write($fallbackBody);
                return $response
                    ->withHeader('Content-Type', 'image/png')
                    ->withStatus(200);
            }
        }
        return $response->withStatus(404);
    }

    $response->getBody()->write($body);
    return $response
        ->withHeader('Content-Type', $contentType ?: 'image/png')
        ->withStatus(200);
});
