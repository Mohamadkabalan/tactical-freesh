<?php
add_action('rafflepress_giveaway_webhooks','rafflepress_pro_add_webhooks');

function rafflepress_pro_add_webhooks($args) {
	
	
    $add_webhooks = false;
	if(!empty($args["settings"]->add_webhooks)){
        $add_webhooks = $args["settings"]->add_webhooks;
    }
	
	if($add_webhooks == true){
        if (!empty($args["settings"]->webhook_items)) {
            $webhook_items = $args["settings"]->webhook_items;
        
            foreach ($webhook_items as $t=> $value) {
                $webhooks_url = $value->webhooks_url;
            
            
                if ($webhooks_url!='') {
                    $headers = [];

                    $body = [
                    'fullname'=> $args["name"],
                    'first_name' => $args["first_name"],
                    'last_name' => $args["last_name"],
                    'email'=> $args["email"],
                    'giveaway_id' => $args["giveaway_id"],
                    'giveaway_name' => $args["giveaway"]->name,
                    'sign_up_date'	=> date('Y-m-d H:i:s'),
                ];
                
                    //$method = $value->webhooks_method;
                    $format = $value->webhooks_request_format;
                    if ("json"== $format) {
                        $body = wp_json_encode($body);
                        //$headers['Content-Type'] = 'application/json; charset=utf-8';
                    }

                    if (isset($value->header)) {
                        foreach ($value->header as $k => $val) {
                            $parameter_keys = $val->parameter_keys;
                            $parameter_value = $val->parameter_value;
                        
                            if ($parameter_keys!="") {
                                $headers[$parameter_keys] = $parameter_value;
                            }
                        }
                    }

                    $options = [
                    //'method'      => $method,
                    'timeout'     => MINUTE_IN_SECONDS,
                    'redirection' => 0,
                    'httpversion' => '1.0',
                    'blocking'    => true,
                    'user-agent'  => sprintf('rafflepress-webhooks'),
                    'headers'     => $headers,
                    'body'        => $body,
                ];
                    $response = wp_remote_post($webhooks_url, $options);
                }
            }
        }

	}

    
	
}

