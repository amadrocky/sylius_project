<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">Sylius Standard Edition</h1>

<p align="center">This is Sylius Standard Edition repository for starting new projects.</p>

Installation
------------

```bash
$ composer install
$ yarn install
$ yarn build
$ php bin/console doctrine:database:create
$ sylius fixtures:load
$ php bin/console doctrine:shema:update --force
$ symfony serve
$ open http://localhost:8000/
```
Demarrage
------------

Configurer le fichier .env ou creer un fichichier .env.local

```bash
DATABASE_URL=mysql://user:password@127.0.0.1/database

API_KEY= Add your api key
```

Stripe
------------
Les produits de base sont au nombre 3.
6 mois à 9.99.
12 mois à 29.99.
24 mois à 39.99.
Créer les produit dans Stripe pour pouvoir les renseigner (id) dans le fichier PaymentController.php dans la méthode paymentProcess.

Routes
------------

Client (utilisateur) => http://127.0.0.1:8000/subscription
Admin => Page d'acceuil admin ou => http://127.0.0.1:8000/admin/subscriptions

Comptes
------------

Utilisateur (client)
Email: shop@example.com
Password: sylius

Admin
Email: sylius@example.com
Password: sylius