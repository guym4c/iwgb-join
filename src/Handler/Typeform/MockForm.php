<?php

namespace Iwgb\Join\Handler\Typeform;

use Guym4c\TypeformAPI\Model\Resource\Form;
use Guym4c\TypeformAPI\Model\Utils\Field\FieldType;
use Guym4c\TypeformAPI\TypeformApiException;
use Handlebars\Context;
use Handlebars\Handlebars;
use Handlebars\Template;
use Iwgb\Join\Provider\Provider;
use Parsedown;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;
use Teapot\StatusCode;

class MockForm extends AbstractTypeformHandler {

    private const METADATA_QUERY_KEYS = [
        'id' => true,
        'data' => true,
    ];

    private Handlebars $view;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->view = $c[Provider::VIEW];
    }

    /**
     * {@inheritdoc}
     * @throws TypeformApiException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {
        if ($this->settings['isProd']) {
            return $response->withStatus(StatusCode::FORBIDDEN);
        }

        $formId = $request->getQueryParam('id');
        if (empty($formId)) {
            return $response->withStatus(StatusCode::BAD_REQUEST);
        }

        $aid = $this->getApplicant($request)->getId();

        $form = Form::get($this->typeform, $formId);

        $this->addLabelParserHelper($form, $request);

        $completionUrl = '';
        if (!empty($form->settings->redirectAfterSubmitUrl)) {
            $completionUrl = $form->settings->redirectAfterSubmitUrl;
        } else {
            foreach ($form->thankyouScreens as $thankyouScreen) {
                if (!empty($thankyouScreen->redirectUrl)) {
                    $completionUrl = $thankyouScreen->redirectUrl;
                    break;
                }
            }
        }

        foreach ($form->fields as $field) {
            $field->type = $this->generateTypeArray($field->type);
        }

        $response->getBody()->write(
            $this->view->render('typeformMock', [
                'form' => $form,
                'completionUri' => Uri::createFromString($completionUrl)->getPath(),
                'dataUri' => $request->getQueryParam('data', ''),
                'formUrl' => $this->getTypeformUrl($request, $formId),
                'aid' => $aid,
            ])
        );

        return $response;
    }

    private function generateTypeArray(string $targetType) {
        $types = [];
        foreach (FieldType::getAll() as $type) {
            $types[$type] = $type === $targetType;
        }
        return $types;
    }

    private function addLabelParserHelper(Form $form, Request $request): void {
        $queryData = array_diff_key($request->getQueryParams(), self::METADATA_QUERY_KEYS);
        $this->view->addHelper(
            'parseHidden',
            function (Template $template, Context $context) use ($form, $queryData): string {
                $label = (new Parsedown())
                    ->text($context->get('title'));
                return $template->render([
                    'title' => preg_replace_callback(
                        '/{{hidden:(?<name>[A-z]+)}}/',
                        fn (array $matches): string => $queryData[$matches['name']] ?? $matches[0],
                        $label,
                    ),
                ]);
            },
        );
    }
}