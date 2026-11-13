from faker import Faker
import json
import random

fake = Faker('zh_TW')
Faker.seed(10)
random.seed(10)

seed_data = {
    "Staff": [],
    "login_info": [],
    "Customer": [],
    "Supplier": [],
    "Invertory": [],
    "Purchase": [],
    "Purchase_details": [],
    "Order": [],
    "Order_details": [],
    "Sequences": [
         {"name": "Order", "year": "2026", "last_num": "3"}, 
         {"name": "Purchase", "year": "2026", "last_num": "3"}
    ],
}

# Staff
dept_name = ["CS", "管理層"]
for i in range(1, 11):
    seed_data["Staff"].append({
        "_ref": f"staff_{i}",
        "name": fake.name(),
        "dept": random.choice(dept_name),
        "phone": fake.phone_number(),
    })

# login data
for i in range(1, 5):
    staff_ref = f"staff_{i}"
    staff = next((s for s in seed_data["Staff"] if s["_ref"] == staff_ref), None)
    seed_data["login_info"].append({
        "staff_id": f"@staff_{i}",
        "password": "test123",
        "role": "admin" if staff["dept"] == "管理層" else "staff",
    })

# Customer
for i in range(1, 5):
    random_staff = random.choice(seed_data["Staff"])
    seed_data["Customer"].append({
        "_ref": f"cust_{i}",
        "co_name": fake.company(),
        "contact_name": fake.name(),
        "phone": fake.phone_number(),
        "staff_id": f"@{random_staff['_ref']}",
    })

# Supplier
for i in range(1, 5):
    seed_data["Supplier"].append({
        "_ref": f"sup_{i}",
        "co_name": fake.company(),
        "contact_name": fake.name(),
        "phone": fake.phone_number(),
    })
        
# 貨存
goods_name = ["原子筆", "橡皮擦", "筆記本", "釘書機", "剪刀"]
used = set()
for i in range(1, 11):
    name = goods_name[i % 5]
    while True:
        random_sup = random.choice(seed_data["Supplier"])
        combine = (name, random_sup["_ref"])
        if combine not in used:
            used.add(combine)
            break
    
    seed_data["Invertory"].append({
        "_ref": f"inv_{i}",
        "name": name,
        "quantity": random.randint(1, 10),
        "price": random.randint(15, 100),
        "sup_id": f"@{random_sup['_ref']}",
    })

# Purchase
for i in range(1, 4):
    random_staff = random.choice(seed_data["Staff"])
    seed_data["Purchase"].append({
        "_ref": f"pur_{i}",
        "date": fake.date_between(start_date="-6m", end_date="+7d").strftime("%Y-%m-%d"),
        "staff_id": f"@{random_staff['_ref']}",
    })

# Purchase Details
for i in range(1, 4):
    total_amount = 0
    item_num = random.randint(1, 5)

    for j in range(1, item_num):
        random_goods = random.choice(seed_data["Invertory"])
        unit = random.randint(1, 5)
        price = random_goods["price"]
        amount = unit * price
        total_amount += amount

        seed_data["Purchase_details"].append({
            "pu_id": f"@pur_{i}",
            "goods_id": f"@{random_goods['_ref']}",
            "unit": unit,
            "price": price,
            "amount": amount
        })

        match_pur = next(o for o in seed_data["Purchase"] if o["_ref"] == f"pur_{i}")
        match_pur["amount"] = total_amount

# Order
for i in range(1, 4):
    random_staff = random.choice(seed_data["Staff"])
    random_cust = random.choice(seed_data["Customer"])
    seed_data["Order"].append({
        "_ref": f"order_{i}",
        "date": fake.date_between(start_date="-6m", end_date="+7d").strftime("%Y-%m-%d"),
        "staff_id": f"@{random_staff['_ref']}",
        "cust_id": f"@{random_cust['_ref']}",
    })

# Order Details
for i in range(1, 4):
    total_amount = 0
    item_num = random.randint(1, 5)

    for j in range(1, item_num):
        random_goods = random.choice(seed_data["Invertory"])
        unit = random.randint(1, 5)
        price = random_goods["price"] - 20
        price = 13 if price <= 0 else price
        amount = unit * price
        total_amount += amount

        seed_data["Order_details"].append({
            "order_id": f"@order_{i}",
            "goods_id": f"@{random_goods['_ref']}",
            "unit": unit,
            "price": price,
            "amount": amount
        })

    match_order = next(o for o in seed_data["Order"] if o["_ref"] == f"order_{i}")
    match_order["amount"] = total_amount

# 防止偷入進去
seed_data["Staff"].append({
    "_ref": f"staff_10",
    "name": fake.name(),
    "dept": "清潔部門",
    "phone": fake.phone_number(),
})

with open('init/seed_data.json', 'w', encoding='utf-8') as f:
    json.dump(seed_data, f, ensure_ascii=False, indent=2)