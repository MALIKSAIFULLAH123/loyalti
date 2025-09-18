<?php

namespace MetaFox\Newsletter\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Newsletter\Http\Requests\v1\Newsletter\Admin\IndexRequest;
use MetaFox\Newsletter\Http\Requests\v1\Newsletter\Admin\SendTestRequest;
use MetaFox\Newsletter\Http\Requests\v1\Newsletter\Admin\StoreRequest;
use MetaFox\Newsletter\Http\Requests\v1\Newsletter\Admin\UpdateRequest;
use MetaFox\Newsletter\Http\Resources\v1\Newsletter\Admin\CreateNewsletterForm;
use MetaFox\Newsletter\Http\Resources\v1\Newsletter\Admin\NewsletterDetail as Detail;
use MetaFox\Newsletter\Http\Resources\v1\Newsletter\Admin\NewsletterItemCollection as ItemCollection;
use MetaFox\Newsletter\Repositories\NewsletterAdminRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Newsletter\Http\Controllers\Api\NewsletterAdminController::$controllers;.
 */

/**
 * Class NewsletterAdminController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class NewsletterAdminController extends ApiController
{
    /**
     * @var NewsletterAdminRepositoryInterface
     */
    private NewsletterAdminRepositoryInterface $repository;

    /**
     * NewsletterAdminController Constructor.
     *
     * @param NewsletterAdminRepositoryInterface $repository
     */
    public function __construct(NewsletterAdminRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     *
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->getNewsletters($params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        $data = $this->repository->createNewsletter($context, $params);

        Artisan::call('cache:reset');
        return $this->success(new Detail($data), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url' => '/newsletter/newsletter/browse',
                ],
            ]], __p('newsletter::phrase.newsletter_created_successfully'));
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show($id): Detail
    {
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    /**
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateNewsletter(user(), $id, $params);
        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [], __p('newsletter::phrase.newsletter_updated_successfully'));
    }

    public function process(int $id): JsonResponse
    {
        $newsletter = $this->repository->find($id);

        $this->repository->processNewsletter($newsletter);

        return $this->success([], [], __p('newsletter::phrase.newsletter_processed_successfully'));
    }

    public function reprocess(int $id): JsonResponse
    {
        $newsletter = $this->repository->find($id);

        $this->repository->reprocessNewsletter($newsletter);

        return $this->success([], [], __p('newsletter::phrase.newsletter_reprocessed_successfully'));
    }

    public function resend(int $id): JsonResponse
    {
        $newsletter = $this->repository->find($id);

        $this->repository->resendNewsletter($newsletter);

        return $this->success(new Detail($newsletter->refresh()), [], __p('newsletter::phrase.newsletter_resend_successfully'));
    }

    public function stop(int $id): JsonResponse
    {
        $newsletter = $this->repository->find($id);

        $this->repository->stopNewsletter($newsletter);

        return $this->success(new Detail($newsletter->refresh()), [], __p('newsletter::phrase.newsletter_stopped_successfully'));
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteNewsletter(user(), $id);

        return $this->success([], [], __p('newsletter::phrase.newsletter_deleted_successfully'));
    }

    /**
     * View creation form.
     *
     * @return CreateNewsletterForm
     */
    public function create(): CreateNewsletterForm
    {
        return new CreateNewsletterForm();
    }

    public function test(SendTestRequest $request, int $id): JsonResponse
    {
        $params     = $request->validated();
        $recipients = Arr::get($params, 'recipients');
        $newsletter = $this->repository->sendTest($recipients, $id);

        return $this->success(new Detail($newsletter), [], __p('newsletter::phrase.newsletter_send_successfully'));
    }
}
