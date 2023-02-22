<?php
/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 1.2                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2021 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

//paypal dependencies start->
require_once __DIR__ . '/payment/Checkout-PHP-SDK-develop/vendor/autoload.php';
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

//<-paypal dependencies end
//authorize.net dependencies start->
require_once __DIR__ . '/payment/authorize.net-sdk-php-master/vendor/autoload.php';
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

//<-authroize.net dependencies end
//yookassa dependencies start->
require_once __DIR__ . '/payment/yookassa-sdk-php-master/vendor/autoload.php';
use YooKassa\Client;

//<-yookassa dependencies end
class JAK_payment
{
    private function pay_with_stripe($amount, $currency, $id, $name, $type, $action, $success_page, $cancel_page, $stripe_secret, $stripe_publishable)
    {
        /*
        1. $type is used to specify the type of transaction i.e. single or subscription payment
        2. $action is used to specify the action to be taken on the transaction in case of
        subscription method i.e. buy,update,cancel
        3. $success_page is used to specify the url where the user will be redirected after the successful transaction
        4. $cancel_page is used to specify the url where the user will be redirected after the transaction fails,
        either by user end or server end.
        */
        switch ($type)
        {
            case 'single':
                /*
                $amount is used to specify the amount of product
                $currency is used to specify the currency of payment
                $id is used to specify the id of product
                $name is used to specify the name of product
                $type = single
                $action can be anything
                */
                $stripe_publishable = '"' . $stripe_publishable . '"';
                require_once __DIR__ . '/payment/stripe-php-master/init.php';
                \Stripe\Stripe::setApiKey($stripe_secret);
                //header('Content-Type: application/json');
                $checkout_session = \Stripe\Checkout\Session::create(['payment_method_types' => ['card'], 'line_items' => [['price_data' => ['currency' => $currency, 'unit_amount' => ($amount * 100) , 'product_data' => ['name' => $name, ], ], 'description' => $id, 'quantity' => '1', ]], 'mode' => 'payment', 'success_url' => $success_page, 'cancel_url' => $cancel_page, ]);
                $response = json_encode(['id' => $checkout_session->id]); ?>
            <script src="https://polyfill.io/v3/polyfill.min.js?version=3.52.1&features=fetch"></script>
            <script src="https://js.stripe.com/v3/"></script>
            <script>
                var stripe = Stripe(<?php echo $stripe_publishable ?>);
                let response = <?php echo $response ?>;
                stripe.redirectToCheckout({sessionId: response.id}).then(function(result){
                    if(result.error){
                        alert(result.error.message);
                    }
                })

            </script>
            <?php
            break;
            case 'recurring':
                switch ($action)
                {
                    case 'buy':
                        //$amount => can be left empty
                        //$currency =>can be left empty
                        //$id will take the id of subscription
                        //$name => can be left empty
                        //$type => recurring
                        //$action => buy
                        try
                        {
                            require_once __DIR__ . '/payment/stripe-php-master/init.php';
                            \Stripe\Stripe::setApiKey($stripe_secret);
                            //header('Content-Type: application/json');
                            $checkout_session = \Stripe\Checkout\Session::create(['success_url' => $success_page . "?session_id={CHECKOUT_SESSION_ID}", 'cancel_url' => $cancel_page, 'payment_method_types' => ['card'], 'mode' => 'subscription', 'line_items' => [['price' => $id, 'quantity' => 1, ]], ]);
                            $response = json_encode(['id' => $checkout_session->id]);
                            $stripe_publishable = '"' . $stripe_publishable . '"'; ?>
                    <script src="https://polyfill.io/v3/polyfill.min.js?version=3.52.1&features=fetch"></script>
                    <script src="https://js.stripe.com/v3/"></script>
                    <script>
                        var stripe = Stripe(<?php echo $stripe_publishable ?>);
                        let response = <?php echo $response ?>;
                        stripe.redirectToCheckout({sessionId: response.id}).then(function(result){
                            if(result.error){
                                alert(result.error.message);
                            }
                        })
                        </script><?php
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                        break;
                    case 'cancel_immediate':
                        //$amount => can be left empty
                        //$currency => can be left empty
                        //$id => session id produced when subscribing to the plan
                        //$name => can be left empty
                        //$type => recurring
                        //$action => cancel_immediate
                        require_once __DIR__ . '/payment/stripe-php-master/init.php';
                        \Stripe\Stripe::setApiKey($stripe_secret);
                        try
                        {
                            $subscription = \Stripe\Checkout\Session::retrieve($id);
                            $subscription_id = $subscription->subscription;
                            $subscription = \Stripe\Subscription::retrieve($subscription_id);
                            $response = $subscription->cancel();
                            try
                            {
                                if ($response->id)
                                {
                                    return true;
                                }
                                return false;
                            }
                            catch(Exception $e)
                            {
                                return false;
                            }
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                        break;
                    case 'cancel_period_end':
                        //$amount => can be left empty
                        //$currency => can be left empty
                        //$id => session id produced when subscribing to the plan
                        //$name => can be left empty
                        //$type => recurring
                        //$action => cancel_period_end
                        require_once __DIR__ . '/payment/stripe-php-master/init.php';
                        \Stripe\Stripe::setApiKey($stripe_secret);
                        try
                        {
                            $subscription = \Stripe\Checkout\Session::retrieve($id);
                            $subscription_id = $subscription->subscription;
                            $response = \Stripe\Subscription::update($subscription_id, ['cancel_at_period_end' => true, ]);
                            try
                            {
                                if ($response->id)
                                {
                                    return true;
                                }
                                return false;
                            }
                            catch(Exception $e)
                            {
                                return false;
                            }
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                        break;
                    case 'update':
                        //$amount => new price id
                        //$currency => can be left empty
                        //$id => session id produced when subscribing to the plan
                        //$name => can be left empty
                        //$type => recurring
                        //$action => update
                        require_once __DIR__ . '/payment/stripe-php-master/init.php';
                        \Stripe\Stripe::setApiKey($stripe_secret);
                        try
                        {
                            $subscription = \Stripe\Checkout\Session::retrieve($id);
                            $subscription_id = $subscription->subscription;
                            $subscription = \Stripe\Subscription::retrieve($subscription_id);
                            $response = \Stripe\Subscription::update($subscription_id, ['cancel_at_period_end' => false, 'proration_behavior' => 'create_prorations', 'items' => [['id' => $subscription
                                ->items
                                ->data[0]->id, 'price' => $amount, ], ], ]);
                            try
                            {
                                if ($response->id)
                                {
                                    return true;
                                }
                                return false;
                            }
                            catch(Exception $e)
                            {
                                return false;
                            }
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                        break;
                    case 'create_plan':
                        /**
                         * $amount contains the amount of plan
                         * $currency takes the currency of plan
                         * $id takes the interval of plan
                         * $name takes the name of plan
                         * $success_page is left empty
                         * $cancel_page is left empty
                         */
                        require_once __DIR__ . '/payment/stripe-php-master/init.php';
                        \Stripe\Stripe::setApiKey($stripe_secret);
                        try
                        {
                            $stripe = new \Stripe\StripeClient($stripe_secret);
                            $response = $stripe
                                ->products
                                ->create(['name' => $name, ]);
                            try
                            {
                                $product_ID = $response->id;
                                $response = $stripe
                                    ->plans
                                    ->create(['amount' => ($amount * 100) , 'currency' => $currency, 'interval' => $id, 'interval_count' => $success_page, 'product' => $product_ID]);
                                try
                                {
                                    return $response->id;
                                }
                                catch(Exception $e)
                                {
                                    return false;
                                }
                            }
                            catch(Exception $e)
                            {
                                return false;
                            }
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                        break;
                    case 'delete_plan':
                        try
                        {
                            require_once __DIR__ . '/payment/stripe-php-master/init.php';
                            \Stripe\Stripe::setApiKey($stripe_secret);
                            $stripe = new \Stripe\StripeClient($stripe_secret);
                            $response = $stripe
                                ->plans
                                ->delete($id, []);
                            var_dump($response);
                            if ($response->deleted == true)
                            {
                                return true;
                            }
                            else
                            {
                                return false;
                            }
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                        break;
                    case 'check_plan':
                        /**
                         * $id contains the plan id
                         * $name contains the date
                         */
                        require_once __DIR__ . '/payment/stripe-php-master/init.php';
                        \Stripe\Stripe::setApiKey($stripe_secret);
                        try
                        {
                            $stripe = new \Stripe\StripeClient(
                              $stripe_secret
                            );
                            $response = $stripe->subscriptions->retrieve(
                              $id,
                              []
                            );

                            if ($response->status == "active")
                            {

                                // Get the ending date of the subscription
                                $subsend = $response->current_period_end;

                                // Get the starting date of the subscription
                                $subsstart = $response->current_period_start;

                                // Calculate in time stamp from the database
                                $paidunix = strtotime($name);

                                // Now let's compare if we have a valid date
                                if ($subsend > $paidunix && $subsstart < $paidunix) {
                                    return true;
                                } else {
                                    return  false;
                                }
                            }
                            else
                            {
                                return false;
                            }

                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                        break;
                    }
                break;
            }
    }
    private function pay_with_paypal($amount, $currency, $prodID, $prodName, $payment_type, $action, $success_page, $cancel_page, $clientId, $clientSecret, $sandbox)
    {
        /*
        1. $payment_type is used to specify the type of transaction at hand i.e. single or recurring
        2. $action is used to specify the action to be taken at the transaction in case of subscription
        3. $success_page is used to specify the url where the user will be redirected after successful
        transaction takes place.
        4. $cancel_page is used to specify the url where the user will be redirected after the transaction
        fails either at the customer end or server end.
        */
        switch ($payment_type)
        {
            case 'single':
                /*
                $amount defines the amount of transaction
                $currency defines the currency of transaction
                $prodID defined the ID of product
                $prodName is used to specify the name of product
                */
                try
                {
                    if ($sandbox == true)
                    {
                        $url = "https://api-m.sandbox.paypal.com";
                    }
                    else
                    {
                        $url = "https://api-m.paypal.com";
                    }
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url . "/v1/oauth2/token");
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_USERPWD, $clientId . ':' . $clientSecret);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);
                    curl_close($ch);
                    $response = json_decode($response);
                    $accessToken = $response->access_token;
                    if ($accessToken)
                    {
                        try
                        {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url . "/v2/checkout/orders");
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            $redirect = BASE_URL_ORIG . 'class/payment/paypal_single_success.php?redirect=' . $success_page;

                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'Content-Type: application/json',
                                'Authorization: Bearer ' . $accessToken
                            ));
                            $data = '{
                            "intent": "CAPTURE",
                            "purchase_units": [
                            {
                                "amount": {
                                    "currency_code": "' . $currency . '",
                                    "value": "' . $amount . '"
                                }
                            }
                            ],
                            "application_context": {
                                "return_url": "' . $redirect . '",
                                "cancel_url": "' . $cancel_page . '"
                            }
                        }';
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            $response = json_decode($response);
                            try
                            {
                                header("Location: " . $response->links[1]
                                    ->href);
                                exit();
                            }
                            catch(Exception $e)
                            {
                                return false;
                            }
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                    }
                    else
                    {
                        return false;
                    }
                }
                catch(Exception $e)
                {
                    return false;
                }
            break;
            case 'recurring':
                if ($sandbox)
                {
                    $url = "https://api-m.sandbox.paypal.com";
                }
                else
                {
                    $url = "https://api-m.paypal.com";
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url . "/v1/oauth2/token");
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_USERPWD, $clientId . ':' . $clientSecret);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                $json = json_decode($result);
                $accessToken = $json->access_token;
                curl_close($ch);
                switch ($action)
                {
                    case 'buy':
                        /*
                        1. $amount specifies the price of subscription
                        2. $prodID specifies the id of subscription plan
                        3. $prodName specifies the name of subscription plan
                        4. $currency defines the currency in which the subscription plan is created
                        */
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url . "/v1/billing/subscriptions");
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Content-Type: application/json",
                            "Authorization: Bearer " . $accessToken
                        ));
                        $date = date("Y\-m\-d\Th:i:s\Z");
                        $data = '{
                        "plan_id": "' . $prodID . '",
                        "start_time": "' . $date . '",
                        "application_context": {
                            "return_url": "' . $success_page . '",
                            "cancel_url": "' . $cancel_page . '"
                        }
                    }';
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $response = curl_exec($ch);
                        $response = json_decode($response);
                        // print_r($response);
                        if ($response->status == "APPROVED" && isset($response->id) && !empty($response->id))
                        {
                            return $response->id;
                        }
                        else
                        {
                            return false;
                        }
                    break;
                    case 'update':
                        /*
                        1. $prodID specifies the ID of subscription
                        2. $prodName specifies the ID of subscription plan to which the user is to be upgraded or
                        downgraded.
                        3. $amount specifies the amount of the subscription plan to which the user is to be
                        updated.
                        4. $currency specified the currency of the subscription plan to user is to be updated
                        */
                        if ($sandbox)
                        {
                            $url = "https://api-m.sandbox.paypal.com";
                        }
                        else
                        {
                            $url = "https://api-m.paypal.com";
                        }
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url . "/v1/billing/subscriptions/" . $prodID . "/revise");
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Content-Type: application/json",
                            "Authorization: Bearer " . $accessToken
                        ));
                        $data = '{
                        "plan_id": "' . $prodName . '",
                        "shipping_amount": {
                            "currency_code": "' . $currency . '",
                            "value": "' . $amount . '"
                            },
                            "application_context": {
                                "shipping_preference": "SET_PROVIDED_ADDRESS",
                                "payment_method": {
                                    "payer_selected": "PAYPAL",
                                    "payee_preferred": "IMMEDIATE_PAYMENT_REQUIRED"
                                    },
                                    "return_url": "' . $success_page . '",
                                    "cancel_url": "' . $cancel_page . '"
                                }
                            }';
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $response = curl_exec($ch);
                        $response = json_decode($response);
                        header("Location: " . $response->links[0]
                            ->href);
                        exit();
                    break;
                    case 'pause':
                        /*
                        1. $prodID specifies the ID of subscription
                        2. $prodName specifies the reason of suspending the transaction
                        */
                        if ($sandbox)
                        {
                            $url = "https://api-m.sandbox.paypal.com";
                        }
                        else
                        {
                            $url = "https://api-m.paypal.com";
                        }
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url . "/v1/billing/subscriptions/" . $prodID . "/suspend");
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Content-Type: application/json",
                            "Authorization: Bearer " . $accessToken
                        ));
                        $data = '{
                        "reason": "' . $prodName . '"
                    }';
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $response = curl_exec($ch);
                        if ($response == "")
                        {
                            return true;
                        }
                        return false;
                    break;
                    case 'cancel':
                        /*
                        1. $prodID specifies the ID of subscription
                        2. $prodName specifies the reason of cancelling the subscription
                        */
                        if ($sandbox)
                        {
                            $url = "https://api-m.sandbox.paypal.com";
                        }
                        else
                        {
                            $url = "https://api-m.paypal.com";
                        }
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url . "/v1/billing/subscriptions/" . $prodID . "/cancel");
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Content-Type: application/json",
                            "Authorization: Bearer " . $accessToken
                        ));
                        $data = '{
                        "reason": "' . $prodName . '"
                    }';
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $response = curl_exec($ch);
                        if ($response == "")
                        {
                            return true;
                        }
                        return false;
                    break;
                    case 'activate':
                        /*
                        1. $prodID specifies the id of subcription
                        2. $prodName specifies the reason of reactiviting a previously suspended/paused subscription
                        */
                        if ($sandbox)
                        {
                            $url = "https://api-m.sandbox.paypal.com";
                        }
                        else
                        {
                            $url = "https://api-m.paypal.com";
                        }
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url . "/v1/billing/subscriptions/" . $prodID . "/activate");
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Content-Type: application/json",
                            "Authorization: Bearer " . $accessToken
                        ));
                        $data = '{
                        "reason": "' . $prodName . '"
                    }';
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $response = curl_exec($ch);
                        if ($response == "")
                        {
                            return true;
                        }
                        return false;
                    break;
                    case 'create_plan':
                        /**
                         * $amount,$currency,$prodID,$prodName,$payment_type,$action,$success_page,$cancel_page
                         * $amount contains the plan price
                         * $currency contains the currency of the plan
                         * $prodID contains the description of subscription plan
                         * $prodName contains the name of subscription
                         * $success_page contains the interval
                         */
                        if ($sandbox)
                        {
                            $url = "https://api-m.sandbox.paypal.com";
                        }
                        else
                        {
                            $url = "https://api-m.paypal.com";
                        }
                        try
                        {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url . "/v1/catalogs/products");
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                            curl_setopt($ch, CURLOPT_VERBOSE, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'Content-Type: application/json',
                                'Authorization: Bearer ' . $accessToken
                            ));
                            $data = '{
                            "name": "' . $prodName . ' Product",
                            "description": "Subscription Plan product",
                            "type": "service",
                            "category": "software"
                        }';
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            $response = json_decode($response);
                            try
                            {
                                $product_ID = $response->id;
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $url . "/v1/billing/plans");
                                curl_setopt($ch, CURLOPT_POST, true);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($ch, CURLOPT_VERBOSE, true);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                    'Content-Type: application/json',
                                    'Authorization: Bearer ' . $accessToken
                                ));
                                $data = '{
                                "product_id": "' . $product_ID . '",
                                "name": "' . $prodName . '",
                                "description": "' . $prodID . '",
                                "status": "ACTIVE",
                                "billing_cycles": [
                                {
                                    "frequency": {
                                        "interval_unit": "' . strtoupper($success_page) . '",
                                        "interval_count": ' . $cancel_page . '
                                        },
                                        "tenure_type": "REGULAR",
                                        "sequence": 1,
                                        "total_cycles": 0,
                                        "pricing_scheme": {
                                            "fixed_price": {
                                                "value": "' . $amount . '",
                                                "currency_code": "' . $currency . '"
                                            }
                                        }
                                    }
                                    ],
                                    "payment_preferences": {
                                        "auto_bill_outstanding": true,
                                        "payment_failure_threshold": 3
                                    }
                                }';
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $response = curl_exec($ch);
                                $response = json_decode($response);
                                try
                                {
                                    return $response->id;
                                }
                                catch(Exception $e)
                                {
                                    return false;
                                }
                            }
                            catch(Exception $e)
                            {
                                return false;
                            }
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                    break;
                    case 'delete_plan':
                        if ($sandbox)
                        {
                            $url = "https://api-m.sandbox.paypal.com";
                        }
                        else
                        {
                            $url = "https://api-m.paypal.com";
                        }
                        try
                        {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url . "/v1/billing/plans/" . $prodID . "/deactivate");
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_VERBOSE, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'Content-Type: application/json',
                                'Authorization: Bearer ' . $accessToken
                            ));
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            $response = json_decode($response);
                            try
                            {
                                if ($response == NULL)
                                {
                                    return true;
                                }
                                else
                                {
                                    return false;
                                }
                            }
                            catch(Exception $e)
                            {
                                return false;
                            }
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                    break;
                    case 'check_plan':
                        /**
                         * $id contains the plan id
                         * $name contains the date
                         */
                        if ($sandbox)
                        {
                            $url = "https://api-m.sandbox.paypal.com";
                        }
                        else
                        {
                            $url = "https://api-m.paypal.com";
                        }
                        try
                        {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url . "/v1/billing/subscriptions/" . $prodID);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_VERBOSE, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'Content-Type: application/json',
                                'Authorization: Bearer ' . $accessToken
                            ));
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            $response = json_decode($response);
                            try
                            {
                                if ($response->status == "ACTIVE")
                                {

                                    // Get the ending date of the subscription
                                    $subsupdate = strtotime($response->status_update_time);

                                    // Calculate in time stamp from the database
                                    $paidunix = strtotime($name);

                                    // Now let's compare if we have a valid date
                                    if ($subsupdate > $paidunix) {
                                        return true;
                                    } else {
                                        return  false;
                                    }
                                }
                                else
                                {
                                    return false;
                                }
                            }
                            catch(Exception $e)
                            {
                                return false;
                            }
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                    break;
                    case 'update_plan':
                        if ($sandbox)
                        {
                            $url = "https://api-m.sandbox.paypal.com";
                        }
                        else
                        {
                            $url = "https://api-m.paypal.com";
                        }
                        try
                        {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url . "/v1/billing/plans/" . $prodID . "/update-pricing-schemes");
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_VERBOSE, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'Content-Type: application/json',
                                'Authorization: Bearer ' . $accessToken
                            ));
                            $data = '{
                                "pricing_schemes": [
                                {
                                    "billing_cycle_sequence": 1,
                                    "pricing_scheme": {
                                        "fixed_price": {
                                            "value": "' . $amount . '",
                                            "currency_code": "' . $currency . '"
                                        }
                                    }
                                }
                                ]
                            }';
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            $response = json_decode($response);
                            try
                            {
                                if ($response == NULL)
                                {
                                    return true;
                                }
                                else
                                {
                                    return false;
                                }
                            }
                            catch(Exception $e)
                            {
                                return false;
                            }
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                }
            break;
        }
    }
    private function pay_with_authorize($amount, $currency, $prodID, $prodName, $payment_type, $action, $success_page, $cancel_page, $loginID, $transactionKey, $sandbox)
    {
        /*
        API LOGIN ID: 2xQ7rs3KfSZ
        TRANSACTION KEY: 86Ns29u4JS6Sny3P
        KEY: Simon
        */
        define("AUTHORIZENET_LOG_FILE", "phplog");
        switch ($payment_type)
        {
            case 'single':
                /* Create a merchantAuthenticationType object with authentication details
                 retrieved from the constants file */

                /*
                $amount = amount of transaction
                $currency is equal to card number in this case
                $prodId = ID of product
                $prodName = name of product
                $paymentType = single
                $action can be left empty
                $success_page is card expiration date in this case
                $cancel_page is card code in this case
                
                */

                $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
                $merchantAuthentication->setName($loginID);
                $merchantAuthentication->setTransactionKey($transactionKey);

                // Set the transaction's refId
                $refId = 'ref' . time();

                // Create the payment data for a credit card
                $creditCard = new AnetAPI\CreditCardType();
                $creditCard->setCardNumber($currency);
                $creditCard->setExpirationDate($success_page);
                $creditCard->setCardCode($cancel_page);

                // Add the payment data to a paymentType object
                $paymentOne = new AnetAPI\PaymentType();
                $paymentOne->setCreditCard($creditCard);

                // Create order information
                $order = new AnetAPI\OrderType();
                $order->setInvoiceNumber($prodID);
                $order->setDescription($prodName);

                // Add values for transaction settings
                $duplicateWindowSetting = new AnetAPI\SettingType();
                $duplicateWindowSetting->setSettingName("duplicateWindow");
                $duplicateWindowSetting->setSettingValue("60");

                // Create a TransactionRequestType object and add the previous objects to it
                $transactionRequestType = new AnetAPI\TransactionRequestType();
                $transactionRequestType->setTransactionType("authCaptureTransaction");
                $transactionRequestType->setAmount($amount);
                $transactionRequestType->setOrder($order);
                $transactionRequestType->setPayment($paymentOne);
                $transactionRequestType->addToTransactionSettings($duplicateWindowSetting);

                // Assemble the complete transaction request
                $request = new AnetAPI\CreateTransactionRequest();
                $request->setMerchantAuthentication($merchantAuthentication);
                $request->setRefId($refId);
                $request->setTransactionRequest($transactionRequestType);

                // Create the controller and get the response
                $controller = new AnetController\CreateTransactionController($request);
                if ($sandbox)
                {
                    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
                }
                else
                {
                    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
                }

                if ($response != null)
                {
                    // Check to see if the API request was successfully received and acted upon
                    if ($response->getMessages()
                        ->getResultCode() == "Ok")
                    {
                        // Since the API request was successful, look for a transaction response
                        // and parse it to display the results of authorizing the card
                        $tresponse = $response->getTransactionResponse();

                        if ($tresponse != null && $tresponse->getMessages() != null)
                        {
                            return true;
                        }
                        else
                        {
                            return false;
                        }
                        // Or, print errors if the API request wasn't successful
                        
                    }
                    else
                    {
                        return false;
                    }
                }
                return false;
            break;
            case 'recurring':
                switch ($action)
                {
                    case 'buy':

                        /*
                        parameters:  $amount,$currency,$prodID,$prodName,$payment_type,$action,$success_page,$cancel_page
                        $amount = amount to be charged at the time of buying subscription
                        $currency implies to customer name
                        $prodID implies to start date of the subscription
                        $prodName implies to the name of subscription plan
                        $payment_type = recurring
                        $action = buy
                        $success_page implies to credit card number
                        $cancel_page imples to credit card expiration date
                        */
                        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
                        $merchantAuthentication->setName($loginID);
                        $merchantAuthentication->setTransactionKey($transactionKey);

                        // Set the transaction's refId
                        $refId = 'ref' . time();

                        // Subscription Type Info
                        $subscription = new AnetAPI\ARBSubscriptionType();
                        $subscription->setName($prodName);

                        $interval = new AnetAPI\PaymentScheduleType\IntervalAType();
                        $interval->setLength("1");
                        $interval->setUnit("months");

                        $paymentSchedule = new AnetAPI\PaymentScheduleType();
                        $paymentSchedule->setInterval($interval);
                        $paymentSchedule->setStartDate($prodID);
                        $paymentSchedule->setTotalOccurrences("9999");

                        $subscription->setPaymentSchedule($paymentSchedule);
                        $subscription->setAmount($amount);

                        $creditCard = new AnetAPI\CreditCardType();
                        $creditCard->setCardNumber($success_page);
                        $creditCard->setExpirationDate($cancel_page);

                        $payment = new AnetAPI\PaymentType();
                        $payment->setCreditCard($creditCard);
                        $subscription->setPayment($payment);

                        $Name = explode(" ", $currency);
                        $billTo = new AnetAPI\NameAndAddressType();
                        $billTo->setFirstName($Name[0]);
                        $billTo->setLastName($Name[1]);

                        $subscription->setBillTo($billTo);

                        $request = new AnetAPI\ARBCreateSubscriptionRequest();
                        $request->setmerchantAuthentication($merchantAuthentication);
                        $request->setRefId($refId);
                        $request->setSubscription($subscription);
                        $controller = new AnetController\ARBCreateSubscriptionController($request);
                        if ($sandbox)
                        {
                            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
                        }
                        else
                        {
                            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
                        }

                        if (($response != null) && ($response->getMessages()
                            ->getResultCode() == "Ok"))
                        {
                            return $response->getSubscriptionId();
                        }
                        return false;
                    break;
                    case 'update':

                        /*
                        parameters:  $amount,$currency,$prodID,$prodName,$payment_type,$action,$success_page,$cancel_page
                        $amount = amount to be charged at the time of updating subscription
                        $currency is to be left empty
                        $prodID implies the id of subscription
                        $prodName implies to the name of new subscription plan
                        $payment_type = recurring
                        $action = update
                        $success_page is to be left empty
                        $cancel_page is to be left empty
                        */
                        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
                        $merchantAuthentication->setName($loginID);
                        $merchantAuthentication->setTransactionKey($transactionKey);

                        // Set the transaction's refId
                        $refId = 'ref' . time();

                        $subscription = new AnetAPI\ARBSubscriptionType();

                        $subscription->setName($prodName);
                        $subscription->setAmount($amount);
                        //set customer profile information
                        //$subscription->setProfile($profile);
                        $request = new AnetAPI\ARBUpdateSubscriptionRequest();
                        $request->setMerchantAuthentication($merchantAuthentication);
                        $request->setRefId($refId);
                        $request->setSubscriptionId($prodID);
                        $request->setSubscription($subscription);

                        $controller = new AnetController\ARBUpdateSubscriptionController($request);

                        if ($sandbox)
                        {
                            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
                        }
                        else
                        {
                            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
                        }
                        if (($response != null) && ($response->getMessages()
                            ->getResultCode() == "Ok"))
                        {
                            return true;
                        }
                        return false;
                    break;
                    case 'cancel':

                        /*
                        parameters:  $amount,$currency,$prodID,$prodName,$payment_type,$action,$success_page,$cancel_page
                        $amount is to be left empty
                        $currency is to be left empty
                        $prodID implies the id of subscription
                        $prodName is to be left empty
                        $payment_type = recurring
                        $action = cancel
                        $success_page is to be left empty
                        $cancel_page is to be left empty
                        */
                        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
                        $merchantAuthentication->setName($loginID);
                        $merchantAuthentication->setTransactionKey($transactionKey);

                        // Set the transaction's refId
                        $refId = 'ref' . time();

                        $request = new AnetAPI\ARBCancelSubscriptionRequest();
                        $request->setMerchantAuthentication($merchantAuthentication);
                        $request->setRefId($refId);
                        $request->setSubscriptionId($prodID);

                        $controller = new AnetController\ARBCancelSubscriptionController($request);

                        if ($sandbox)
                        {
                            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
                        }
                        else
                        {
                            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
                        }
                        if (($response != null) && ($response->getMessages()
                            ->getResultCode() == "Ok"))
                        {
                            return true;
                        }
                        return false;
                    break;
                }
        }
    }
    private function pay_with_yoomoney($amount, $currency, $prodID, $prodName, $payment_type, $action, $success_page, $cancel_page, $shopID, $secretKey)
    {

        switch ($payment_type)
        {
            case 'single':
                /*
                 *$amount is the amount to be charged
                 *$currency is the currency of transaction
                 *$prodID is not used
                 *$prodName contains thee description of transaction
                 *$payment_type = single
                 *$action is not used
                 *$success_page is used as returnUrl
                 *$cancel_page is not used
                */
                $client = new Client();
                $client->setAuth($shopID, $secretKey);
                $payment = $client->createPayment(array(
                    'amount' => array(
                        'value' => $amount,
                        'currency' => $currency,
                    ) ,
                    'confirmation' => array(
                        'type' => 'redirect',
                        'return_url' => $success_page,
                    ) ,
                    'capture' => true,
                    'description' => $prodName,
                ) , uniqid('', true));
                try
                {
                    $_SESSION["yoomoney"] = $payment->_id;
                    header("Location: " . $payment
                        ->confirmation
                        ->confirmation_url);
                    exit();
                }
                catch(Exception $e)
                {
                    return false;
                }
            break;
            case 'recurring':
                switch ($action)
                {
                    case 'buy':
                        /*
                         *$amount is the amount to be charged
                         *$currency is the currency of transaction
                         *$prodID is not used
                         *$prodName contains thee description of transaction
                         *$payment_type = recurring
                         *$action = buy
                         *$success_page is used as returnUrl
                         *$cancel_page is not used
                        */
                        $client = new Client();
                        $client->setAuth($shopID, $secretKey);
                        $payment = $client->createPayment(array(
                            'amount' => array(
                                'value' => floatval($amount) ,
                                'currency' => $currency,
                            ) ,
                            'payment_method_data' => array(
                                'type' => 'bank_card',
                            ) ,
                            'confirmation' => array(
                                'type' => 'redirect',
                                'return_url' => $success_page,
                            ) ,
                            'capture' => true,
                            'description' => $prodName,
                            'save_payment_method' => true,
                        ) , uniqid('', true));
                        try
                        {   
                            $_SESSION["yoomoney"] = $payment->_id;
                            header("Location: " . $payment
                                ->confirmation
                                ->confirmation_url);
                            exit();
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                    break;
                    case 'charge':
                        /*
                         *$amount is the amount to be charged
                         *$currency is the currency of transaction
                         *$prodID contains the id of previously saved description
                         *$prodName contains thee description of transaction
                         *$payment_type = recurring
                         *$action = charge
                         *$success_page is left empty
                         *$cancel_page is not used
                        */
                        $client = new Client();
                        $client->setAuth($shopID, $secretKey);
                        $payment = $client->createPayment(array(
                            'amount' => array(
                                'value' => floatval($amount) ,
                                'currency' => $currency,
                            ) ,
                            'capture' => true,
                            'payment_method_id' => $prodID,
                            'description' => $prodName,
                        ) , uniqid('', true));
                        if ($payment->status == "succeeded")
                        {
                            return true;
                        }
                        else
                        {
                            return false;
                        }
                    break;
                }
        }
    }
    private function pay_with_paystack($amount, $currency, $prodID, $prodName, $payment_type, $action, $success_page, $cancel_page, $secretKey)
    {
        switch ($payment_type)
        {
            case 'single':
                /*
                $amount contains the amount to be charged
                $currency contains the currency of transaction
                $prodID contains the id of product
                $prodName contains the name of product
                $action contains the email of customer
                $payment_type = single
                $success_page contains the url where user will be redirected after the transactions succeeds
                $cancel_page contains the url where user will be redirected in case the user cancels the transaction
                */
                if ($amount == "" || $currency == "" || $prodID == "" || $prodName == "" || $action == "" || $success_page == "" || $cancel_page == "")
                {
                    return false;
                }
                try
                {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/initialize");
                    curl_Setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_VERBOSE, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        "Authorization: Bearer " . $secretKey,
                        "Content-Type: application/json"
                    ));
                    $data = '{ 
                    "email": "' . $action . '",
                    "amount": "' . (floatval($amount) * 100) . '",
                    "currency": "' . $currency . '",
                    "callback_url": "' . $success_page . '",
                    "metadata": {
                        "cart_id": "' . $prodID . '",
                        "custom_fields": [
                        {
                            "display_name": "Product Name",
                            "variable_name": "product_name",
                            "value": "' . $prodName . '"
                        }
                        ],
                        "cancel_action": "' . $cancel_page . '"
                    }
                }';
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_Setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);
                    $response = json_decode($response);
                    // die(var_dump($response));
                    if ($response->status == true)
                    {
                        try
                        {
                            header("Location: " . $response
                                ->data
                                ->authorization_url);
                            exit();
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                    }
                    return false;
                }
                catch(Exception $e)
                {
                    return false;
                }
            break;

            case 'create_plan':
                /*
                $amount contains the amount to be charged
                $currency contains the currency of transaction
                $prodName contains the name of product
                $action contains the email of customer
                $payment_type = single
                $success_page contains the url where user will be redirected after the transactions succeeds
                $cancel_page contains the url where user will be redirected in case the user cancels the transaction
                */

                // paystack has a strange way to charge customers. Let's modify the plans
                // We get the details that worked for all others but not for paystack Interval in words. Valid intervals are: daily, weekly, monthly, biannually, annually
                // Month
                $paystack_interval = $success_page;
                // How often
                $paystack_howoften = $cancel_page;

                if ($paystack_interval == "week")
                {
                    $paystack_interval = "weekly";
                }
                elseif ($paystack_interval == "month" && $paystack_howoften == 1)
                {
                    $paystack_interval = "monthly";
                }
                elseif ($paystack_interval == "month" && $paystack_howoften == 6)
                {
                    $paystack_interval = "biannually";
                }
                else
                {
                    $paystack_interval = "annually";
                }

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/plan");
                curl_Setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Authorization: Bearer " . $secretKey,
                    "Content-Type: application/json"
                ));
                $data = '{
                    "name": "' . $prodID . '",
                    "interval": "' . $paystack_interval . '",
                    "amount": "' . (floatval($amount) * 100) . '",
                    "currency": "' . $currency . '"
                }';
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_Setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                $response = json_decode($response);
                // var_dump($response->message);
                // var_dump($response->data->plan_code);
                // die(print_r($response));
                if ($response->status)
                {
                    return $response->data->plan_code;
                }
                else
                {
                    return false;
                }
            break;
            case 'buy_plan':
                /*
                    1. $action specifies the email address of the customer
                    2. $prodID specifies the id of subscription plan
                */
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/subscription");
                curl_Setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Authorization: Bearer " . $secretKey,
                    "Content-Type: application/json"
                ));
                $date = date("Y\-m\-d\Th:i:s\Z");
                $data = '{
                        "customer": "' . $action . '",
                        "plan": "' . $prodID . '",
                        "start_date": "' . $date . '"
                        
                    }';
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                $response = json_decode($response);
                // die(print_r($response));
                if ($response->status == true && isset($response->data->email_token) && !empty($response->data->email_token))
                {
                    return $response->data->subscription_code.'_-_'.$response->data->email_token;
                }
                else
                {
                    return false;
                }
            break;
            case 'cancel_plan':
                /*
                    1. $prodID specifies the id of subscription plan
                */

                // Paystack is special again
                $pscancel = explode("_-_", $prodID);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/subscription/disable");
                curl_Setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Authorization: Bearer " . $secretKey,
                    "Content-Type: application/json"
                ));
                $data = '{
                        "code": "' . $pscancel[0] . '",
                        "token": "' . $pscancel[1] . '"
                        
                    }';
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                $response = json_decode($response);
                // die(print_r($response));
                if ($response->status == true)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            break;
            case 'check_plan':
                /**
                    * $prodID contains the plan id
                    * $prodName contains the date
                */

                // Paystack is special again
                $pscancel = explode("_-_", $prodID);

                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.paystack.co/subscription/".$pscancel[0],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer " . $secretKey,
                    "Cache-Control: no-cache",
                ),
                ));
                 
                $response = curl_exec($curl);
                $response = json_decode($response);

                // Get the ending date of the subscription
                $subsupdate = strtotime($response->data->updatedAt);

                // Calculate in time stamp from the database
                $paidunix = strtotime($prodName);

                // die(print_r($response));
                if ($response->status == true && $response->data->status == "active" && $subsupdate > $paidunix)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            break;
        }
    }
    private function pay_with_verifone($amount, $currency, $prodID, $prodName, $payment_type, $action, $card_type, $info, $code, $key)
    {
        switch ($payment_type)
        {
            case 'single':
                /**
                 * $amount is used to specify the amount of transaction
                 * $currency is used to specify the currency of transaction
                 * $prodID specifies the description of transaction
                 * $prodName specifies the name of product
                 * $payment_type = single
                 * $action specifies the card number
                 * $card_type specifies the card type
                 * $info specifies the card expiry date, ccid, holder name and email in following format month:year:ccid:name:email
                 */

                try
                {
                    if ($amount == "" || $currency == "" || $prodID == "" || $prodName == "" || !is_array($info))
                    {
                        return false;
                    }
                    $date = gmdate('Y-m-d H:i:s');
                    $hash_string = strlen($code) . $code . strlen($date) . $date;
                    $hash = hash_hmac('md5', $hash_string, $key);
                    try
                    {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, "https://api.2checkout.com/rest/6.0/orders");
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_VERBOSE, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'X-Avangate-Authentication: code="' . $code . '" date="' . $date . '" hash="' . $hash . '"',
                            'Content-Type: application/json',
                            'Accept: application/json'
                        ));
                        $data = '{
                        "Country": "us",
                        "Currency": "' . $currency . '",
                        "ExternalReference": "REST_API_AVANGTE",
                        "Language": "en",
                        "Source": "' . BASE_URL . '",
                        "BillingDetails": {
                            "Address1": "Address",
                            "City": "City",
                            "State": "State",
                            "CountryCode": "US",
                            "Email": "' . $info['email'] . '",
                            "FirstName": "' . $info['name'] . '",
                            "LastName": "' . $info['name'] . '",
                            "Zip": "12345"
                            },
                            "Items": [
                            {
                                "Name": "' . $prodName . '",
                                "Description": "' . $prodID . '",
                                "Quantity": 1,
                                "IsDynamic": true,
                                "Tangible": false,
                                "PurchaseType": "PRODUCT",
                                "Price": {
                                    "Amount": ' . floatval($amount) . ',
                                    "Type": "CUSTOM"
                                }
                            }
                            ],
                            "PaymentDetails": {
                                "Type": "' . $prodName . '",
                                "Currency": "' . $currency . '",
                                "PaymentMethod": {
                                "CardNumber": "'.$action.'",
                                "CardType": "'.$card_type.'",
                                "Vendor3DSReturnURL": "www.success.com",
                                "Vendor3DSCancelURL": "www.fail.com",
                                "ExpirationYear": "'.$info['Year'].'",
                                "ExpirationMonth": "'.$info['Month'].'",
                                "CCID": "'.$info['CCID'].'",
                                "HolderName": "'.$fname." ".$lname.'",
                                "RecurringEnabled": false,
                                "HolderNameTime": 1,
                                "CardNumberTime": 1
                                }
                            }
                        }';
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $response = curl_exec($ch);
                        $response = json_decode($response);

                        die(var_dump($response));
                        try
                        {
                            if ($response->ApproveStatus == "AUTHRECEIVED")
                            {
                                return true;
                            }
                            else
                            {
                                return false;
                            }
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                    }
                    catch(Exception $e)
                    {
                        return false;
                    }
                }
                catch(Exception $e)
                {
                    return false;
                }
                break;
            case 'recurring':
                switch ($action)
                {
                    case 'buy':
                        /**
                         * $amount contains the amount of recurring transaction
                         * $currency contains the currency of recurring transaction
                         * $prodID contains the productCode of subscription plan
                         * $prodName will be left empty
                         * $payment_type = recurring
                         * $action = buy
                         * $card_type contains the card type
                         * $info contains the cardNumber, expiry month, expiry year, ccid, card holder name, email, recurring period
                         */
                        if ($amount == "" || $currency == "" || $prodID == "" || $card_type == "" || !is_array($info))
                        {
                            return false;
                        }
                        if (count($info) < 7)
                        {
                            return false;
                        }
                        $date = gmdate('Y-m-d H:i:s');
                        $hash_string = strlen($code) . $code . strlen($date) . $date;
                        $hash = hash_hmac('md5', $hash_string, $key);
                        try
                        {
                            if (strtolower($info['Period']) == "month")
                            {
                                $cycles = 36;
                            }
                            else if (strtolower($info['Period']) == "day")
                            {
                                $cycles = 1095;
                            }
                            else
                            {
                                return false;
                            }
                            $name = explode(" ", $info['Name']);
                            if (count($name) >= 2)
                            {
                                $fname = $name[0];
                                $lname = $name[1];
                            }
                            else if (count($name) < 2)
                            {
                                $fname = $name[0];
                                $lname = "";
                            }
                            $start_date = date("Y-m-d");
                            $end_date = explode("-", $start_date);
                            $end_date[0] = (int)$end_date[0];
                            $end_date[0] += 3;
                            $end_date[0] = (string)$end_date[0];
                            $end_date = implode("-", $end_date);

                            //getting product id and product name using the productCode
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, "https://api.2checkout.com/rest/6.0/products/" . $prodID . "/");
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_VERBOSE, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'X-Avangate-Authentication: code="' . $code . '" date="' . $date . '" hash="' . $hash . '"',
                                'Accept: application/json',
                                'ProductCode: ' . $prodID
                            ));
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            $response = json_decode($response);
                            $productID = $response->AvangateId;
                            $productName = $response->ProductName;

                            //buying subscription request
                            $ch = curl_init("https://api.2checkout.com/rest/6.0/subscriptions");
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_VERBOSE, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'X-Avangate-Authentication: code="' . $code . '" date="' . $date . '" hash="' . $hash . '"',
                                'Content-Type: application/json',
                                'Accept: application/json'
                            ));
                            $data = '{
                            "CustomPriceBillingCyclesLeft": ' . $cycles . ',
                            "EndUser": {
                                "Address1": "Address",
                                "Address2": "",
                                "City": "City",
                                "Company": "",
                                "CountryCode": "us",
                                "Email": "' . $info['Email'] . '",
                                "Fax": "",
                                "FirstName": "' . $fname . '",
                                "Language": "en",
                                "LastName": "' . $lname . '",
                                "Phone": "",
                                "State": "CA",
                                "Zip": "12345"
                                },
                                "ExpirationDate": "' . $end_date . '",
                                "ExternalSubscriptionReference": "' . uniqid() . '",
                                "NextRenewalPrice": ' . $amount . ',
                                "NextRenewalPriceCurrency": "' . $currency . '",
                                "PartnerCode": "",
                                "Payment": {
                                  "CCID": "' . $info['CCID'] . '",
                                  "CardNumber": "' . $info['CardNumber'] . '",
                                  "CardType": "' . $card_type . '",
                                  "ExpirationMonth": "' . $info['Month'] . '",
                                  "ExpirationYear": "' . $info['Year'] . '",
                                  "HolderName": "' . $info['Name'] . '"
                                  },
                                  "Product": {
                                      "ProductId": "' . $productID . '",
                                      "ProductName": "' . $productName . '",
                                      "ProductQuantity": 1,
                                      "ProductVersion": ""
                                      },
                                      "RecurringEnabled": true,
                                      "SubscriptionEnabled": true,
                                      "StartDate": "' . $start_date . '",
                                      "SubscriptionValue": ' . $amount . ',
                                      "SubscriptionValueCurrency": "' . $currency . '",
                                      "Test": 1
                                  }';
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            if ($response)
                            {
                                $response = json_decode($response);
                                return $response;
                            }
                            return false;
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                        break;
                    case 'update':
                        /**
                         * $amount contains the amount of recurring transaction
                         * $currency contains the currency of recurring transaction
                         * $prodID contains the productCode of subscription plan
                         * $prodName will be left empty
                         * $payment_type = recurring
                         * $action = buy
                         * $card_type contains the card type
                         * $info contains the cardNumber, expiry month, expiry year, ccid, card holder name, email, recurring period, subscription reference
                         */
                        if ($amount == "" || $currency == "" || $prodID == "" || $card_type = "" || !is_array($info))
                        {
                            return false;
                        }
                        if (count($info) < 8)
                        {
                            return false;
                        }
                        $date = gmdate('Y-m-d H:i:s');
                        $hash_string = strlen($code) . $code . strlen($date) . $date;
                        $hash = hash_hmac('md5', $hash_string, $key);
                        try
                        {
                            if (strtolower($info['Period']) == "month")
                            {
                                $cycles = 36;
                            }
                            else if (strtolower($info['Period']) == "day")
                            {
                                $cycles = 1095;
                            }
                            else
                            {
                                return false;
                            }
                            $name = explode(" ", $info['Name']);
                            if (count($name) >= 2)
                            {
                                $fname = $name[0];
                                $lname = $name[1];
                            }
                            else if (count($name) < 2)
                            {
                                $fname = $name[0];
                                $lname = "";
                            }
                            $start_date = date("Y-m-d");
                            $end_date = explode("-", $start_date);
                            $end_date[0] = (int)$end_date[0];
                            $end_date[0] += 3;
                            $end_date[0] = (string)$end_date[0];
                            $end_date = implode("-", $end_date);
                            //getting product id and product name using the productCode
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, "https://api.2checkout.com/rest/6.0/products/" . $prodID . "/");
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_VERBOSE, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'X-Avangate-Authentication: code="' . $code . '" date="' . $date . '" hash="' . $hash . '"',
                                'Accept: application/json',
                                'ProductCode: ' . $prodID
                            ));
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            $response = json_decode($response);
                            $productID = $response->AvangateId;
                            $productName = $response->ProductName;

                            //sending update request
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, "https://api.2checkout.com/rest/6.0/subscriptions/" . $info['SubscriptionReference']);
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'X-Avangate-Authentication: code="' . $code . '" date="' . $date . '" hash="' . $hash . '"',
                                'Content-Type: application/json',
                                'Accept: application/json',
                                'SubscriptionReference: ' . $info['SubscriptionReference']
                            ));
                            $data = '{
                            "EndUser": {
                                "Address1": "Address",
                                "Address2": "",
                                "City": "City",
                                "Company": "",
                                "CountryCode": "us",
                                "Email": "' . $info['Email'] . '",
                                "Fax": "",
                                "FirstName": "' . $fname . '",
                                "Language": "en",
                                "LastName": "' . $lname . '",
                                "Phone": "",
                                "State": "CA",
                                "Zip": "12345"
                                },
                                "ExpirationDate": "' . $end_date . '",
                                "Product": {
                                    "ProductId": "' . $productID . '",
                                    "ProductName": "' . $productName . '",
                                    "ProductQuantity": 1
                                    },
                                    "RecurringEnabled": true,
                                    "SubscriptionEnabled": true
                                }';
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            $response = json_decode($response);
                            return $response;
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                        break;
                    case 'cancel':
                        if (!is_array($info))
                        {
                            return false;
                        }
                        if (count($info) < 1)
                        {
                            return false;
                        }
                        $date = gmdate('Y-m-d H:i:s');
                        $hash_string = strlen($code) . $code . strlen($date) . $date;
                        $hash = hash_hmac('md5', $hash_string, $key);
                        try
                        {

                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, "https://api.2checkout.com/rest/6.0/subscriptions/" . $info['SubscriptionReference']);
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'X-Avangate-Authentication: code="' . $code . '" date="' . $date . '" hash="' . $hash . '"',
                                'Content-Type: application/json',
                                'Accept: application/json',
                                'SubscriptionReference: ' . $info['SubscriptionReference']
                            ));
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            $response = json_decode($response);
                            return $response;
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                        break;
                    case 'create_plan':
                        /**
                         * $amount contains the amount of subscription
                         * $currency contains the currency of subscription plan
                         * $prodID contains the description of subscription plan
                         * $prodName contains the name of subscription plan
                         * $card_type contains the interval of subscription plan
                         */
                        $date = gmdate('Y-m-d H:i:s');
                        $hash_string = strlen($code) . $code . strlen($date) . $date;
                        $hash = hash_hmac('md5', $hash_string, $key);
                        try
                        {
                            $productCode = uniqid();
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, "https://api.2checkout.com/rest/6.0/products/");
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_VERBOSE, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'X-Avangate-Authentication: code="' . $code . '" date="' . $date . '" hash="' . $hash . '"',
                                'Accept: application/json',
                                'Content-Type: application/json'
                            ));
                            if (strtolower($card_type) == "m")
                            {
                                $interval = 36;
                            }
                            else if (strtolower($card_type) == "h")
                            {
                                $interval = 1095;
                            }
                            $data = '{
                            "BundleProducts": [],
                            "Enabled": true,
                            "GeneratesSubscription": true,
                            "GiftOption": false,
                            "LongDescription": "\r\n\t<span style=\"color: rgb(0, 0, 0); font-family: Arial, Helvetica, sans; font-size: 11px; line-height: 14px; text-align: justify;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus orci a nulla dignissim, ut egestas nunc blandit. Nunc porta lorem sed dui placerat lobortis. Suspendisse rutrum justo enim, et mattis ex convallis at. Nullam vehicula justo nunc, non maximus purus pharetra a. Ut ipsum velit, efficitur ac laoreet a, pellentesque et lorem. Morbi ac nibh ut lectus tempus ullamcorper tincidunt in justo. Aenean viverra euismod cursus. Donec rhoncus laoreet ligula nec euismod.",
                            "Platforms": [
                            {
                                "Category": "Mobile",
                                "IdPlatform": "23",
                                "PlatformName": "Android"
                                },
                                {
                                    "Category": "Mobile",
                                    "IdPlatform": "20",
                                    "PlatformName": "iPhone"
                                    },
                                    {
                                        "Category": "Desktop",
                                        "IdPlatform": "32",
                                        "PlatformName": "Windows 10"
                                    }
                                    ],
                                    "Prices": [],
                                    "PricingConfigurations": [
                                    {
                                        "BillingCountries": [],
                                        "Code": "54DCBC3DC8",
                                        "Default": true,
                                        "DefaultCurrency": "' . $currency . '",
                                        "Name": "2Checkout Subscriptions Price Configuration Marius",
                                        "PriceOptions": [
                                        {
                                            "Code": "SUPPORT",
                                            "Required": true
                                            },
                                            {
                                                "Code": "USERS",
                                                "Required": true
                                                },
                                                {
                                                    "Code": "BACKUP",
                                                    "Required": false
                                                }
                                                ],
                                                "PriceType": "NET",
                                                "Prices": {
                                                  "Regular": [
                                                  {
                                                      "Amount": ' . floatval($amount) . ',
                                                      "Currency": "' . $currency . '",
                                                      "MaxQuantity": "99999",
                                                      "MinQuantity": "1",
                                                      "OptionCodes": []
                                                  }
                                                  ],
                                                  "Renewal": []
                                                  },
                                                  "PricingSchema": "DYNAMIC"
                                              }
                                              ],
                                              "ProductCategory": "Audio & Video",
                                              "ProductCode": "' . $productCode . '",
                                              "ProductName": "' . $prodName . '",
                                              "ProductType": "REGULAR",
                                              "ProductVersion": "1.0",
                                              "PurchaseMultipleUnits": true,
                                              "ShortDescription": "\r\n\tLorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus orci a nulla dignissim, ut egestas nunc blandit.&nbsp;",
                                              "SubscriptionInformation": {
                                                  "BillingCycle": "' . $interval . '",
                                                  "BillingCycleUnits": "' . $card_type . '",
                                                  "BundleRenewalManagement": "GLOBAL",
                                                  "ContractPeriod": {
                                                    "Action": "RESTART",
                                                    "EmailsDuringContract": true,
                                                    "IsUnlimited": true,
                                                    "Period": -1
                                                    },
                                                    "DeprecatedProducts": [],
                                                    "IsOneTimeFee": false,
                                                    "RenewalEmails": {
                                                        "Settings": {
                                                          "AutomaticRenewal": {
                                                            "After15Days": false,
                                                            "After5Days": false,
                                                            "Before15Days": false,
                                                            "Before1Day": false,
                                                            "Before30Days": false,
                                                            "Before7Days": true,
                                                            "OnExpirationDate": true
                                                            },
                                                            "ManualRenewal": {
                                                                "After15Days": false,
                                                                "After5Days": false,
                                                                "Before15Days": false,
                                                                "Before1Day": false,
                                                                "Before30Days": false,
                                                                "Before7Days": true,
                                                                "OnExpirationDate": true
                                                            }
                                                            },
                                                            "Type": "CUSTOM"
                                                            },
                                                            "UsageBilling": 0
                                                            },
                                                            "SystemRequirements": "",
                                                            "TrialDescription": "",
                                                            "TrialUrl": ""
                                                        }';
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            $response = json_decode($response);
                            try
                            {
                                if ($response == true)
                                {
                                    return $productCode;
                                }
                                else
                                {
                                    return false;
                                }
                            }
                            catch(Exception $e)
                            {
                                return false;
                            }
                        }
                        catch(Exception $e)
                        {
                            var_dump($e);
                            return false;
                        }
                        break;
                    case 'delete_plan':
                        //$prodID contains the productCode of subscription plan
                        $date = gmdate('Y-m-d H:i:s');
                        $hash_string = strlen($code) . $code . strlen($date) . $date;
                        $hash = hash_hmac('md5', $hash_string, $key);
                        try
                        {
                            //getting product ID and product name
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, "https://api.2checkout.com/rest/6.0/products/" . $prodID . "/");
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'X-Avangate-Authentication: code="' . $code . '" date="' . $date . '" hash="' . $hash . '"',
                                'Accept: application/json',
                                'ProductCode: ' . $prodID,
                                "Content-Type: application/json"
                            ));
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            $response = json_decode($response);
                            try
                            {
                                if ($response == true)
                                {
                                    return true;
                                }
                                else
                                {
                                    return false;
                                }
                            }
                            catch(Exception $e)
                            {
                                return false;
                            }
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                        break;
                    case 'update_plan':
                        //$prodId corresponds to the productCode of subscription plan
                        //$prodName contains the new name of subscription plan
                        $date = gmdate('Y-m-d H:i:s');
                        $hash_string = strlen($code) . $code . strlen($date) . $date;
                        $hash = hash_hmac('md5', $hash_string, $key);
                        try
                        {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, "https://api.2checkout.com/rest/6.0/products/" . $prodID . "/");
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'X-Avangate-Authentication: code="' . $code . '" date="' . $date . '" hash="' . $hash . '"',
                                'Accept: application/json',
                                'ProductCode: ' . $prodID,
                                "Content-Type: application/json",
                                "Accept: application/json"
                            ));
                            $data = '{
                                                            "Enabled": true,
                                                            "ProductName": "' . $prodName . '"
                                                        }';
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            $response = json_decode($response);
                            var_dump($response);
                            try
                            {
                                if ($response == true)
                                {
                                    return true;
                                }
                                else
                                {
                                    return false;
                                }
                            }
                            catch(Exception $e)
                            {

                            }
                        }
                        catch(Exception $e)
                        {

                        }
                        break;
                    }
                    break;
                }
            }
            public function JAK_pay($platform, $amount, $currency, $prodID, $prodName, $payment_type, $action, $success_page, $cancel_page, $secret_one, $secret_two, $sandbox)
            {
                $platform = strtolower($platform);
                switch ($platform)
                {
                    case 'stripe':
                        return $this->pay_with_stripe($amount, $currency, $prodID, $prodName, $payment_type, $action, $success_page, $cancel_page, $secret_one, $secret_two);
                    break;
                    case 'paypal':
                        return $this->pay_with_paypal($amount, $currency, $prodID, $prodName, $payment_type, $action, $success_page, $cancel_page, $secret_one, $secret_two, $sandbox);
                    break;
                    case 'verifone':
                        return $this->pay_with_verifone($amount, $currency, $prodID, $prodName, $payment_type, $action, $success_page, $cancel_page, $secret_one, $secret_two);
                    break;
                    case 'authorize.net':
                        return $this->pay_with_authorize($amount, $currency, $prodID, $prodName, $payment_type, $action, $success_page, $cancel_page, $secret_one, $secret_two, $sandbox);
                    break;
                    case 'yoomoney':
                        return $this->pay_with_yoomoney($amount, $currency, $prodID, $prodName, $payment_type, $action, $success_page, $cancel_page, $secret_one, $secret_two);
                    break;
                    case 'paystack':
                        return $this->pay_with_paystack($amount, $currency, $prodID, $prodName, $payment_type, $action, $success_page, $cancel_page, $secret_one);
                    break;
                }
            }

        }
?>