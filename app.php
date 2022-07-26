<?php
require_once('stripe-php/init.php');

$data['key'] = $_POST['data'];

$stripe = new \Stripe\StripeClient($data['key']);

$result = $stripe->prices->all(['limit' => 100]);

echo json_encode($result, JSON_PRETTY_PRINT);



