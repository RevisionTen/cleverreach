# revision-ten/cleverreach

## Installation

#### Install via composer

Run `composer req revision-ten/cleverreach`.

### Add the Bundle

Add the bundle to your AppKernel (Symfony 3.4.\*) or your Bundles.php (Symfony 4.\*).

Symfony 3.4.\* /app/AppKernel.php:
```PHP
new \RevisionTen\Cleverreach\CleverreachBundle(),
```

Symfony 4.\* /config/bundles.php:
```PHP
RevisionTen\Cleverreach\CleverreachBundle::class => ['all' => true],
```

### Configuration

Configure the bundle:

```YAML
# Cleverreach example config.
cleverreach:
    client_id: '123456' # Your cleverreach client id.
    user: 'myaccount@domain.tld' # Your cleverreach account name.
    password: 'supersecret' # Your cleverreach password.
    campaigns:
        dailyNewsletterCampagin:
            list_id: '123456' # Id of your newsletter list.
            form_id: '123456' # Id of your form configuration.
```

### Usage

Use the CleverreachService to subscribe users.

Symfony 3.4.\* example:
```PHP
$cleverreachService = $this->container->get(CleverreachService::class);

$subscribed = $cleverreachService->subscribe('dailyNewsletterCampagin', 'visitor.email@domain.tld', 'My Website', [
    'firstname' => 'John',
    'lastname' => 'Doe',
]);
```
