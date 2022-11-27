<li>{{ $data['car_id'] }}</li>
<li><b>price_usd {{ $data['price_usd'] }} USD</b></li>
<li>VIN {{ $data['vin'] }}</li>
<li>views {{ $data['views'] }}</li>
<li>order_number {{ $data['order_number'] }}</li>
<li>model_id {{ $data['model_id'] }}</li>
<li>order_date {{ $data['order_date'] }}</li>
@foreach($fields as $field)
    <li>{{$field}}: {{ $data[$field] }}</li>
@endforeach
