<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Microsoft Entra ID SSO
|--------------------------------------------------------------------------
|
| Semua kredensial diambil dari file .env di root project.
| Salin .env.example menjadi .env lalu isi nilainya.
|
| Local redirect contoh:
|   MICROSOFT_REDIRECT_URI=http://localhost/siperkul/auth/microsoft_callback
| Production redirect contoh:
|   MICROSOFT_REDIRECT_URI=https://domain-anda.ac.id/auth/microsoft_callback
|
| Jangan commit file .env ke git.
|
*/
$config['microsoft_enabled'] = filter_var(
	getenv('MICROSOFT_ENABLED') !== FALSE ? getenv('MICROSOFT_ENABLED') : 'true',
	FILTER_VALIDATE_BOOLEAN
);

$config['microsoft_tenant_id']     = getenv('MICROSOFT_TENANT_ID') ?: '';
$config['microsoft_client_id']     = getenv('MICROSOFT_CLIENT_ID') ?: '';
$config['microsoft_client_secret'] = getenv('MICROSOFT_CLIENT_SECRET') ?: '';
$config['microsoft_redirect_uri']  = getenv('MICROSOFT_REDIRECT_URI') ?: 'http://localhost/siperkul/auth/microsoft_callback';
$config['microsoft_scopes']        = getenv('MICROSOFT_SCOPES') ?: 'openid profile email User.Read';
$config['microsoft_allowed_domain']= getenv('MICROSOFT_ALLOWED_DOMAIN') ?: 'itpln.ac.id';

$config['microsoft_authorize_url'] = 'https://login.microsoftonline.com/{tenant}/oauth2/v2.0/authorize';
$config['microsoft_token_url']     = 'https://login.microsoftonline.com/{tenant}/oauth2/v2.0/token';
$config['microsoft_graph_me_url']  = 'https://graph.microsoft.com/v1.0/me';
