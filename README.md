# rest-api
API для расчета итоговой стоимости

пример URL : https://comflex.ru/api/v1/order/cost

пример JSON
```bash
{
    "order": {
        "customer": {
            "birthdate": "1945-11-01",
            "gender": "male"
        },
        "delivery": "2025-01-17 16:45",
        "products": [
            {
                "price": 6500.50,
                "quantity": 8
            },
            {
                "price": 4278.96,
                "quantity": 3
            },
            {
                "price": 1499.12,
                "quantity": 12
            }
        ]
    }
}
```
ответ
```bash
{
  "total_cost": {
    "real": 31870.67,
    "format": "31 870,67 руб."
  }
}
```