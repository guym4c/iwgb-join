<?php /** @noinspection PhpUnusedPrivateFieldInspection */

namespace Iwgb\Join\Handler\Api\Error;

use MyCLabs\Enum\Enum;

/**
 * @method static self SESSION_START_FAILED()
 * @method static self NO_JOB_TYPE_PROVIDED()
 * @method static self JOB_TYPE_INVALID()
 * @method static self APPLICANT_INVALID()
 * @method static self CSRF_GC_SESSION_MISMATCH()
 * @method static self CSRF_USER_AGENT_MISMATCH()
 * @method static self RECALLED_APPLICANT_INVALID()
 * @method static self RECALLED_APPLICATION_NOT_STARTED()
 *
 * @method static self NO_GC_FLOW_ID_PROVIDED()
 * @method static self PAYMENT_FAILED_NO_MANDATE()
 * @method static self PAYMENT_FAILED_MANDATE_CREATED()
 * @method static self MMS_INTEGRATION_NO_MANDATE()
 * @method static self MMS_INTEGRATION_MANDATE_CREATED()
 *
 * @method static self UNKNOWN()
 * @method static self FATAL()
 */
final class Error extends Enum {

    private const SESSION_START_FAILED = 40;
    private const NO_JOB_TYPE_PROVIDED = 41;
    private const JOB_TYPE_INVALID = 42;
    private const APPLICANT_INVALID = 43;
    private const CSRF_GC_SESSION_MISMATCH = 44;
    private const CSRF_USER_AGENT_MISMATCH = 45;
    private const RECALLED_APPLICANT_INVALID = 46;
    private const RECALLED_APPLICATION_NOT_STARTED = 47;

    private const NO_GC_FLOW_ID_PROVIDED = 50;
    private const PAYMENT_COULD_NOT_COMPLETE_NO_MANDATE = 51;
    private const PAYMENT_COULD_NOT_COMPLETE_MANDATE_CREATED = 52;
    private const MMS_INTEGRATION_NO_MANDATE = 53;
    private const MMS_INTEGRATION_MANDATE_CREATED = 54;

    private const UNKNOWN = 90;
    private const FATAL = 99;
}