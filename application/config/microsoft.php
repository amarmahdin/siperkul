<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Microsoft Entra ID SSO
|--------------------------------------------------------------------------
|
| Redirect URI (Web) di Azure App Registration:
|   http://localhost/siperkul/auth/microsoft_callback
|
| Ganti microsoft_client_secret dengan Value secret dari Azure
| (bukan Secret ID). Jangan commit secret asli ke git publik.
|
*/
$config['microsoft_enabled']       = TRUE;
$config['microsoft_tenant_id']     = '7b388d18-1900-418c-a5d3-e28d7a9a38e6';
$config['microsoft_client_id']     = '2e9a99a6-cf23-48ed-9a7a-ecc5ca63e89f';
$config['microsoft_client_secret'] = 'YOUR_CLIENT_SECRET';
$config['microsoft_redirect_uri']  = 'http://localhost/siperkul/auth/microsoft_callback';
$config['microsoft_scopes']        = 'openid profile email User.Read';
$config['microsoft_authorize_url'] = 'https://login.microsoftonline.com/{tenant}/oauth2/v2.0/authorize';
$config['microsoft_token_url']     = 'https://login.microsoftonline.com/{tenant}/oauth2/v2.0/token';
$config['microsoft_graph_me_url']  = 'https://graph.microsoft.com/v1.0/me';
