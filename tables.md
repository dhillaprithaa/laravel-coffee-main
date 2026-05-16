# Migration Column Type Changes

## users

| Column     | Before   | After         | Reason                                  |
| ---------- | -------- | ------------- | --------------------------------------- |
| `name`     | `string` | `string(100)` | Reasonable name length limit            |
| `username` | `string` | `string(50)`  | Usernames are short, 50 chars is plenty |

**password_reset_tokens**

| Column  | Before   | After        | Reason                                      |
| ------- | -------- | ------------ | ------------------------------------------- |
| `token` | `string` | `string(64)` | Reset tokens are fixed-length (60-64 chars) |

---

## menus

| Column  | Before    | After             | Reason                                                                  |
| ------- | --------- | ----------------- | ----------------------------------------------------------------------- |
| `name`  | `string`  | `string(100)`     | Reasonable menu name limit                                              |
| `price` | `integer` | `decimal(12,2)`   | Monetary value — integer loses cents, decimal gives 2 decimal precision |
| `stock` | `integer` | `unsignedInteger` | Stock can't be negative                                                 |
| `image` | `string`  | `string(2048)`    | Image paths/URLs can be long                                            |

---

## tables

| Column   | Before    | After                 | Reason                                                          |
| -------- | --------- | --------------------- | --------------------------------------------------------------- |
| `number` | `integer` | `unsignedTinyInteger` | Table numbers are small (1–255 max), unsigned since no negative |

---

## orders

| Column        | Before    | After           | Reason                                                |
| ------------- | --------- | --------------- | ----------------------------------------------------- |
| `invoice`     | `string`  | `string(50)`    | Invoice format like `INV-ABCD-2605161000` fits easily |
| `grand_total` | `integer` | `decimal(12,2)` | Monetary value — needs decimal precision              |

---

## order_items

| Column     | Before    | After                  | Reason                                                     |
| ---------- | --------- | ---------------------- | ---------------------------------------------------------- |
| `qty`      | `integer` | `unsignedSmallInteger` | Quantity is always positive, 65535 max is more than enough |
| `price`    | `integer` | `decimal(12,2)`        | Monetary value — needs decimal precision                   |
| `subtotal` | `integer` | `decimal(12,2)`        | Monetary value — needs decimal precision                   |

---

## payments

| Column       | Before   | After  | Reason                                    |
| ------------ | -------- | ------ | ----------------------------------------- |
| `snap_token` | `string` | `text` | Midtrans snap tokens can exceed 255 chars |
