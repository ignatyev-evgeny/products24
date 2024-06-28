
<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grid Template · Bootstrap v5.3</title>




    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">

    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>


</head>
<body class="py-4">


<main>
    <div class="container">
        <div class="row text-center">
            <div class="col-12">
                <table id="productsTable" class="table table-bordered">
                    <thead>
                    <tr>
                        @foreach($fields as $field)
                            <th scope="col">{{ $field }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($products as $product)
                        <tr>
                            @foreach($fields as $key => $field)
                                @if(\Illuminate\Support\Str::contains($key, "PROPERTY_"))
                                    @php
                                        $productFields = collect($product->toArray()['fields']);
                                        $productField = $productFields->where('bitrix_code', $key);
                                        $productField = $productField->first(function ($productField) {
                                            return $productField;
                                        });
                                    @endphp
                                    <td>{{ $productField['value'] }}</td>
                                @else
                                    @php($smallKey = mb_strtolower($key))
                                    <td>{{ $product->$smallKey ?? '' }}</td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
{{--            {{ $products->withQueryString()->links() }}--}}
        </div>
    </div>
</main>

<script>

    $(document).ready( function () {
        $('#productsTable').DataTable();
    });
</script>

</body>
</html>
