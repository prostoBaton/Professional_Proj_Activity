SELECT pod.purchaseorderid, pod.productid, v.name AS vendor_name, v.creditrating
FROM purchaseorderdetail pod
INNER JOIN productvendor pv ON pod.productid = pv.productid
INNER JOIN vendor v ON pv.businessentityid = v.businessentityid
WHERE v.creditrating = 1;