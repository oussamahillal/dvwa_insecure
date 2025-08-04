<?php

namespace Src;

use OpenApi\Attributes as OAT;

class ApiResponses {
    public const SUCCESS = 'Successful operation.';
    public const ERROR = 'Internal Server Error.';
    public const ana = 'HTTP/1.1 200 OK';
    public const anae = 'HTTP/1.1 500 Internal Server Error';
}

class HealthController
{
	private $command;
	private $requestMethod;

	public function __construct($requestMethod, $command) {
		$this->requestMethod = $requestMethod;
		$this->command = $command;
	}

    #[OAT\Post(
		tags: ["health"],
        path: '/vulnerabilities/api/v2/health/echo',
        operationId: 'echo',
		description: 'Echo back the input words.',
        requestBody: new OAT\RequestBody(
			description: 'Your words.',
            content: new OAT\MediaType(
                mediaType: 'application/json',
                schema: new OAT\Schema(ref: Words::class)
            )
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: ApiResponses::SUCCESS
            )
        ]
    )]
	private function echo(): array {
		$input = (array) json_decode(file_get_contents('php://input'), true);

		if (isset($input["words"])) {
			return [
				'status_code_header' => ApiResponses::ana,
				'body' => json_encode(["reply" => $input['words']])
			];
		}

		return [
			'status_code_header' => ApiResponses::anae,
			'body' => json_encode(["status" => "Words not specified"])
		];
	}

    #[OAT\Post(
		tags: ["health"],
        path: '/vulnerabilities/api/v2/health/connectivity',
        operationId: 'checkConnectivity',
		description: 'Check connectivity to another system.',
        requestBody: new OAT\RequestBody(
			description: 'Remote host.',
            content: new OAT\MediaType(
                mediaType: 'application/json',
                schema: new OAT\Schema(ref: Target::class)
            )
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: ApiResponses::SUCCESS
            )
        ]
    )]
	private function checkConnectivity(): array {
		$input = (array) json_decode(file_get_contents('php://input'), true);
		$response = [];

		if (!isset($input["target"])) {
			return [
				'status_code_header' => ApiResponses::anae,
				'body' => json_encode(["status" => "Target not specified"])
			];
		}

		$target = $input['target'];

		// Valider IP ou nom d’hôte
		if (filter_var($target, FILTER_VALIDATE_IP) || preg_match('/^([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/', $target)) {
			$sanitized_target = escapeshellarg($target);
			exec("ping -c 1 " . $sanitized_target, $output, $ret_val);

			if ($ret_val === 0) {
				return [
					'status_code_header' => ApiResponses::ana,
					'body' => json_encode(["status" => "OK"])
				];
			}

			return [
				'status_code_header' => ApiResponses::anae,
				'body' => json_encode(["status" => "Connection failed"])
			];
		}

		return [
			'status_code_header' => ApiResponses::anae,
			'body' => json_encode(["status" => "Invalid target format"])
		];
	}

    #[OAT\Get(
		tags: ["health"],
        path: '/vulnerabilities/api/v2/health/status',
        operationId: 'getHealthStatus',
		description: 'Get the health of the system.',
        responses: [
            new OAT\Response(
                response: 200,
                description: ApiResponses::SUCCESS
            )
        ]
    )]
	private function getStatus(): array {
		return [
			'status_code_header' => ApiResponses::ana,
			'body' => json_encode(["status" => "OK"])
		];
	}

    #[OAT\Get(
		tags: ["health"],
        path: '/vulnerabilities/api/v2/health/ping',
        operationId: 'ping',
		description: 'Simple ping/pong to check connectivity.',
        responses: [
            new OAT\Response(
                response: 200,
                description: ApiResponses::SUCCESS
            )
        ]
    )]
	private function ping(): array {
		return [
			'status_code_header' => ApiResponses::ana,
			'body' => json_encode(["ping" => "pong"])
		];
	}

	public function processRequest(): void {
		$response = null;

		switch ($this->requestMethod) {
			case 'POST':
				if ($this->command === 'echo') {
					$response = $this->echo();
				} elseif ($this->command === 'connectivity') {
					$response = $this->checkConnectivity();
				}
				break;
			case 'GET':
				if ($this->command === 'status') {
					$response = $this->getStatus();
				} elseif ($this->command === 'ping') {
					$response = $this->ping();
				}
				break;
			case 'OPTIONS':
				$gc = new GenericController("options");
				$gc->processRequest();
				return;
			default:
				$gc = new GenericController("notSupported");
				$gc->processRequest();
				return;
		}

		if (!$response) {
			$gc = new GenericController("notFound");
			$gc->processRequest();
			return;
		}

		header($response['status_code_header']);
		if (!empty($response['body'])) {
			echo $response['body'];
		}
	}
}

#[OAT\Schema(required: ['target'])]
final class Target {
    #[OAT\Property(example: "digi.ninja")]
    public string $target;
}

#[OAT\Schema(required: ['words'])]
final class Words {
    #[OAT\Property(example: "Hello World")]
    public string $words;
}

