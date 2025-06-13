{{ config('app.name', 'The Open College') }}
=====================================

{!! $content !!}

---

{{ config('app.name', 'The Open College') }}
Email: {{ config('mail.from.address') }}
Phone: +353 1 234 5678
Website: {{ url('/') }}

This email was sent to {{ $student->email }}.