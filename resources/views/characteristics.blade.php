<li><b>price_usd {{ $data['price_usd'] }} USD</b></li>
<li>views {{ $data['views'] }}</li>
@foreach($fields as $field)
    <li>{{$field}}: {{ $data[$field] }}</li>
@endforeach
