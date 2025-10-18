@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://raw.githubusercontent.com/cridwan/pln-backend/refs/heads/main/public/logo.png" class="logo" alt="PLN Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
