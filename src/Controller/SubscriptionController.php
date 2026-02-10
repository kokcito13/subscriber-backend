<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateSubscriptionRequest;
use App\Dto\StatusResponse;
use App\Dto\SubscriptionResponse;
use App\Dto\UpdateSubscriptionRequest;
use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/v1/subscriptions', name: 'api_v1_subscriptions_')]
class SubscriptionController extends AbstractController
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {}

    #[Route('', name: 'create', methods: ['POST'])]
    public function createSubscription(
        #[MapRequestPayload] CreateSubscriptionRequest $request
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return new JsonResponse(['error' => 'No authenticated user'], Response::HTTP_UNAUTHORIZED);
        }

        $subscription = new Subscription();
        $subscription
            ->setUser($user)
            ->setName($request->name)
            ->setAmount($request->amount)
            ->setCurrency($request->currency)
            ->setBillingPeriod($request->billingPeriod)
            ->setBillingDayOfMonth($request->billingDayOfMonth)
            ->setBillingMonthOfYear($request->billingMonthOfYear)
            ->setNextBillingDate(new \DateTimeImmutable($request->nextBillingDate))
            ->setCategory($request->category);

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        $response = $this->createSubscriptionResponse($subscription);

        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function listSubscriptions(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return new JsonResponse(['error' => 'No authenticated user'], Response::HTTP_UNAUTHORIZED);
        }

        $criteria = ['user' => $user];

        // Add optional filters
        if ($status = $request->query->get('status')) {
            $criteria['status'] = $status;
        }

        if ($category = $request->query->get('category')) {
            $criteria['category'] = $category;
        }

        $subscriptions = $this->subscriptionRepository->findBy($criteria, ['createdAt' => 'DESC']);

        $response = array_map(
            fn(Subscription $subscription) => $this->createSubscriptionResponse($subscription, false),
            $subscriptions
        );

        return new JsonResponse($response);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'])]
    public function showSubscription(string $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return new JsonResponse(['error' => 'No authenticated user'], Response::HTTP_UNAUTHORIZED);
        }

        $subscription = $this->subscriptionRepository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$subscription) {
            return new JsonResponse(['error' => 'Subscription not found'], Response::HTTP_NOT_FOUND);
        }

        $response = $this->createSubscriptionResponse($subscription, true);

        return new JsonResponse($response);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'], requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'])]
    public function updateSubscription(
        string $id,
        #[MapRequestPayload] UpdateSubscriptionRequest $request
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return new JsonResponse(['error' => 'No authenticated user'], Response::HTTP_UNAUTHORIZED);
        }

        $subscription = $this->subscriptionRepository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$subscription) {
            return new JsonResponse(['error' => 'Subscription not found'], Response::HTTP_NOT_FOUND);
        }

        // Update fields if provided
        if ($request->name !== null) {
            $subscription->setName($request->name);
        }
        if ($request->amount !== null) {
            $subscription->setAmount($request->amount);
        }
        if ($request->currency !== null) {
            $subscription->setCurrency($request->currency);
        }
        if ($request->billingPeriod !== null) {
            $subscription->setBillingPeriod($request->billingPeriod);
        }
        if ($request->billingDayOfMonth !== null) {
            $subscription->setBillingDayOfMonth($request->billingDayOfMonth);
        }
        if ($request->billingMonthOfYear !== null) {
            $subscription->setBillingMonthOfYear($request->billingMonthOfYear);
        }
        if ($request->nextBillingDate !== null) {
            $subscription->setNextBillingDate(new \DateTimeImmutable($request->nextBillingDate));
        }
        if ($request->category !== null) {
            $subscription->setCategory($request->category);
        }

        $this->entityManager->flush();

        $response = $this->createSubscriptionResponse($subscription, true);

        return new JsonResponse($response);
    }

    #[Route('/{id}/cancel', name: 'cancel', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'])]
    public function cancelSubscription(string $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return new JsonResponse(['error' => 'No authenticated user'], Response::HTTP_UNAUTHORIZED);
        }

        $subscription = $this->subscriptionRepository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$subscription) {
            return new JsonResponse(['error' => 'Subscription not found'], Response::HTTP_NOT_FOUND);
        }

        $subscription->setStatus(Subscription::STATUS_CANCELED);
        $this->entityManager->flush();

        return new JsonResponse(new StatusResponse(Subscription::STATUS_CANCELED));
    }

    #[Route('/{id}/pause', name: 'pause', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'])]
    public function pauseSubscription(string $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return new JsonResponse(['error' => 'No authenticated user'], Response::HTTP_UNAUTHORIZED);
        }

        $subscription = $this->subscriptionRepository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$subscription) {
            return new JsonResponse(['error' => 'Subscription not found'], Response::HTTP_NOT_FOUND);
        }

        $subscription->setStatus(Subscription::STATUS_PAUSED);
        $this->entityManager->flush();

        return new JsonResponse(new StatusResponse(Subscription::STATUS_PAUSED));
    }

    #[Route('/{id}/resume', name: 'resume', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'])]
    public function resumeSubscription(string $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return new JsonResponse(['error' => 'No authenticated user'], Response::HTTP_UNAUTHORIZED);
        }

        $subscription = $this->subscriptionRepository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$subscription) {
            return new JsonResponse(['error' => 'Subscription not found'], Response::HTTP_NOT_FOUND);
        }

        $subscription->setStatus(Subscription::STATUS_ACTIVE);
        $this->entityManager->flush();

        return new JsonResponse(new StatusResponse(Subscription::STATUS_ACTIVE));
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'])]
    public function deleteSubscription(string $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return new JsonResponse(['error' => 'No authenticated user'], Response::HTTP_UNAUTHORIZED);
        }

        $subscription = $this->subscriptionRepository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$subscription) {
            return new JsonResponse(['error' => 'Subscription not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($subscription);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function createSubscriptionResponse(Subscription $subscription, bool $includeTimestamps = true): SubscriptionResponse
    {
        return new SubscriptionResponse(
            id: $subscription->getId(),
            name: $subscription->getName(),
            amount: $subscription->getAmount(),
            currency: $subscription->getCurrency(),
            billingPeriod: $subscription->getBillingPeriod(),
            billingDayOfMonth: $subscription->getBillingDayOfMonth(),
            billingMonthOfYear: $subscription->getBillingMonthOfYear(),
            nextBillingDate: $subscription->getNextBillingDate()->format('Y-m-d'),
            category: $subscription->getCategory(),
            status: $subscription->getStatus(),
            createdAt: $includeTimestamps ? $subscription->getCreatedAt()?->format('Y-m-d H:i:s') : null,
            updatedAt: $includeTimestamps ? $subscription->getUpdatedAt()?->format('Y-m-d H:i:s') : null
        );
    }
}
