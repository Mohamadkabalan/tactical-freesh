## Woocommerce plugin changes

### v2.3.3

#### Fixes

- Fix surcharge
- Fix select field and add GW checks
- Fix payment method type in request

### v2.3.2

#### Fixes

- Don't set payment failed if id not found for sub

### v2.3.1

#### Fixes

- Use wp current_time function for next date

### v2.3.0

#### Updates

- Payment method change support
- Combined settings page for both payment methods
- Test API connection button
- Database updates for plugin, runnable from settings page
- Disable methods when cart contains mixed items, not when mixed checkout settings is enabled

### v2.2.0

#### Fixes

- Fix surcharge calculation
- Fix refund status setting
- Fix payment method add/delete
- Fix surcharge calculation and response check
- Fix checking for required save method field
- Fix surcharge and

#### Updates

- Map users to customers in GW
- Make token mandatory for subs
- Clarify save payment method handler and fix token usage for subs
- Disable payment method when save cards is disabled for subscriptions
- Don't add surcharge to subscriptions for now

### v2.1.6

- Fix missing file issue

### v2.1.5

- Expand function to get ip address for request

### v2.1.4

- Fix cronjob for subscriptions

### v2.1.3

- Further checks if subscriptions plugin is enabled where applicable

### v2.1.2

- Check if subscriptions plugin is enabled where applicable

### v2.1.1

- Disable gw when sub contains trial period

### v2.1.0

- Integrate GW subscriptions to WooCommerce

#### Fixes and updates

- Validate form only when a new method is used
- Fix billing for daily subscriptions

#### Updates

- Run cron at 3am
- Add message to thank you page for subscriptions
- Disable GW method if daily subscription billing interval is < 3
- Fix gateway filter condition
- Fix cron status assignment
- Fix sensitive info masking
- Combine common elements in subscription and plan builder

### v2.0.2

- Fix surcharge default value

### v2.0.1

- Fix amount conversion and void vs refund condition

### v2.0.0

- Separate gateway.php and includes/*.php files into classes, minimalize procedural code
- Use abstraction for payment methods, keeping it as DRY as possible
- Introduce Builder classes for building requests
- Introduce Client classes for sending requests
- Introduce Handler classes for handling request responses
- Introduce Config classes to provide payment method specific settings
- Introduce GatewayCommand that wraps Builders, Clients and Handlers
- Surcharge:
    - Introduce AJAX for handling surchargeability
    - Surcharge updates with the `onChange` JS event

### v1.1.3

- \[BUG\] Woocommerce 4.8 compatibility

### v1.1.2

- Description about surcharge

### v1.1.1

- Surcharge capabilities
    - Flat surcharge
    - Percentage surcharge
- \[BUG\] CVC fix

### v1.0.2

- \[BUG\] Auth transactions payload fix

### v1.0.1

- First stable release
- eCheck (ACH) and Card transactions
- Save the card mechanism

