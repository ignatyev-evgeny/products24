
<!doctype html>
<html lang="ru" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Товары24</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" >
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
    <script src="//api.bitrix24.com/api/v1/"></script>
</head>
<body class="py-4">
<main>
    <div class="container-fluid">
        <div class="row text-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <button type="button" class="btn btn-primary disabled w-100">Товарные позиции</button>
                            </div>
                            <div class="col-4">
                                <a href="{{ route('productList', ['integration' => $integration->id, 'deal' => $dealId]) }}">
                                    <button type="button" class="btn btn-primary w-100">Товары</button>
                                </a>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-danger w-100 disabled">Настройки</button>
                            </div>
                        </div>
                        <table id="productItemTable" class="table table-striped table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th class="text-center">{{ __("Сделка") }}</th>
                                    <th class="text-center">{{ __("Наименование") }}</th>
                                    <th class="text-center">{{ __("Артикул") }}</th>
                                    <th class="text-center">{{ __("Аналоги") }}</th>
                                    <th class="text-center">{{ __("Стоимость") }}</th>
                                    <th class="text-center">{{ __("Кол-во") }}</th>
                                    <th class="text-center">{{ __("Скидка") }}</th>
                                    <th class="text-center">{{ __("НДС") }}</th>
                                    <th class="text-center">{{ __("Итого") }}</th>
                                    <th class="text-center">{{ __("Действия") }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    $(document).ready( function () {

        BX24.init(function(){
            console.log('Инициализация завершена!', BX24.isAdmin());
        });

        $('#productItemTable').DataTable({
            ajax: '/product-item/{{ $integration->id }}?dealId={{ $dealId }}',
            columns: [
                { data: 'deal', sortable: false, className: 'align-middle', width: "100px" },
                { data: 'productName', sortable: false, className: 'dt-left align-middle' },
                { data: 'article', sortable: false, className: 'dt-left align-middle' },
                { data: 'analogs', sortable: false, className: 'dt-left align-middle' },
                { data: 'amount', className: 'align-middle', sortable: false, width: "150px" },
                { data: 'quantity', className: 'align-middle', sortable: false, width: "100px" },
                { data: 'discount', className: 'align-middle', sortable: false },
                { data: 'tax', className: 'align-middle', sortable: false },
                { data: 'total', className: 'align-middle', sortable: false, width: "150px" },
                { data: 'action', sortable: false, className: 'align-middle', width: "70px" },
            ],
            processing: false,
            serverSide: true,
            pageLength: 100,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Russian.json"
            },
            initComplete: function () {
                BX24.fitWindow();
            }
        });


    });

    function addProductItemToDeal(productItemId, productId, dealId) {
        var count = $('#count_'+productItemId).val();
        var price = $('#price_'+productItemId).val();

        if (typeof count === 'undefined' || count === null || count === '') {
            alert("{{ __("Не указано количество продукции") }}")
            return false;
        }

        if (typeof price === 'undefined' || price === null || price === '') {
            alert("{{ __("Не указана стоимость продукции") }}")
            return false;
        }

        if (typeof productId === 'undefined' || productId === null || productId === '') {
            alert("{{ __("Не указан идентификатор товарной позиции") }}")
            return false;
        }

        if (typeof dealId === 'undefined' || dealId === null || dealId === '') {
            alert("{{ __("Не указан идентификатор сделки") }}")
            return false;
        }

        var button = $("#button_add_product_"+productItemId)

        button.addClass("disabled").html("В работе...");

        addProduct = BX24.callMethod('crm.item.productrow.add', {
            "fields": {
                "ownerId": dealId,
                "ownerType": "D",
                "productId": productId,
                "price": price,
                "quantity": count
            }
        }, function(result) {
            if(result.status !== 200) {
                button.removeClass("disabled").removeClass("btn-success").addClass("btn-danger").html("Ошибка");
                return false;
            }
            button.removeClass("disabled").removeClass("btn-danger").addClass("btn-success").html("Добавить");
            alert("{{ __("Товар успешно добавлен") }}");
        });

    }

</script>
</body>
</html>
