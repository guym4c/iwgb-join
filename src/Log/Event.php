<?php

namespace Iwgb\Join\Log;

class Event {

    public const APPLICANT_CREATED = 'applicant_created';
    public const PLAN_PLACED = 'plan_placed';
    public const REDIRECT_TO_DATA = 'data_redirect';
    public const REDIRECT_TO_BRANCH = 'branch_redirect';
    public const REDIRECT_TO_PAYMENT = 'payment_redirect';

    public const INVALID_JOB_TYPE = 'invalid_job_type';
    public const FLOW_ID_MISSING = 'flow_id_missing';
    public const GOCARDLESS_SESSION_MISMATCH = 'gc_session_mismatch';
}