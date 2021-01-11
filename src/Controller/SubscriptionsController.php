<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionsController extends AbstractController
{
    /**
     * @Route("/admin/subscriptions", name="subscriptions", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        $stripe = new \Stripe\StripeClient(
            'sk_test_51I7qCgIjktDIYiezUfNYo411jpXTPey9JPxQBzojqxMJxHKmUA6XN2czkq5r4dGieTTSZytFtYosvhLReG1m3z3E00GDzfPTIn'
        );

        $subscriptions = [];

        $datas = $stripe->subscriptions->all();

        foreach ($datas->data as $subscription) {
            $subscriptions[] = [
                'id' => $subscription->id,
                'customer' => $stripe->customers->retrieve($subscription->customer, [])->email,
                'price' => $subscription->plan->amount,
                'ending' => (new \DateTime())->setTimestamp($subscription->cancel_at),
            ];
        }

        return $this->render('subscriptions/index.html.twig', [
            'subscriptions' => $subscriptions
        ]);
    }

    /**
     * @Route("/admin/subscriptions/action", name="subscription_actions", methods={"GET"})
     *
     * @return Response
     */
    public function actions(): Response
    {
        $stripe = new \Stripe\StripeClient(
            'sk_test_51I7qCgIjktDIYiezUfNYo411jpXTPey9JPxQBzojqxMJxHKmUA6XN2czkq5r4dGieTTSZytFtYosvhLReG1m3z3E00GDzfPTIn'
        );

        $subscription = [];

        $datas = $stripe->subscriptions->retrieve(
            $_GET['id'],
            []
        );

        $subscription = [
            'id' => $datas->id,
            'customer' => $stripe->customers->retrieve($datas->customer, [])->email,
            'price' => $datas->plan->amount,
            'ending' => (new \DateTime())->setTimestamp($datas->cancel_at),
        ];

        return $this->render('subscriptions/actions.html.twig', [
            'subscription' => $subscription
        ]);
    }
}
