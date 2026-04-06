<?php

namespace App\Http\Requests\Deal;

use Illuminate\Foundation\Http\FormRequest;

class SendProposalRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file'            => ['required_without:send_only', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
            'recipient_email' => ['required_if:send,true', 'email'],
            'subject'         => ['required_if:send,true', 'string', 'max:150'],
            'body'            => ['required_if:send,true', 'string'],
            'send'            => ['sometimes', 'boolean'],
        ];
    }
}
