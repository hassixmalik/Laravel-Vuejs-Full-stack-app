@component('mail::message')
# Invoice #{{ $invoice['id'] }}

**Date:** {{ $invoice['date'] ?? '—' }}  
**Status:** {{ $invoice['status'] }}  

**Customer**  
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

**Subtotal:** {{ number_format($invoice['subtotal'],3) }} BD  
**Discount:** {{ number_format($invoice['discount'],3) }} BD  
**Total:** **{{ number_format($invoice['total'],3) }} BD**

Thanks,<br>
{{ config('app.name') }}
@endcomponent
