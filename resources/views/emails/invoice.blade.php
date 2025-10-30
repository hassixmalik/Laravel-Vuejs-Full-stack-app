@php
  $companyName = config('mail.from.name') ?: config('app.name');
@endphp

@component('mail::message')
# Invoice #{{ $invoice['id'] }}

**From:** {{ $companyName }}  

> Thanks for being a valued customer. Find invoice details below

**Date:** {{ $invoice['date'] ?? '—' }}  
**Status:** {{ $invoice['status'] }}  

**Bill To**  
{{ $invoice['customer']['name'] }}  
{{ $invoice['customer']['email'] }}  
{{ $invoice['customer']['phone'] }}  
{{ $invoice['customer']['address'] }}

@component('mail::table')
| Item | Qty | Price | Total |
|:-----|:---:|------:|------:|
@foreach ($invoice['items'] as $it)
| {{ $it['description'] }} | {{ $it['quantity'] }} | {{ number_format($it['price'],3) }} | {{ number_format($it['total'],3) }} |
@endforeach
@endcomponent

@php
  $discounted = max(($invoice['subtotal'] ?? 0) - ($invoice['discount'] ?? 0), 0);
  $vat = max(($invoice['total'] ?? 0) - $discounted, 0);
@endphp

**Subtotal:** {{ number_format($invoice['subtotal'] ?? 0,3) }} BD  
**Discount:** {{ number_format($invoice['discount'] ?? 0,3) }} BD  
**VAT:** {{ number_format($vat,3) }} BD  
**Total:** **{{ number_format($invoice['total'] ?? 0,3) }} BD**

Thanks,<br>
{{ $companyName }}
@endcomponent
