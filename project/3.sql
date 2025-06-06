SELECT productid, AVG(unitprice) AS avg_unit_price
FROM purchaseorderdetail
GROUP BY productid
HAVING AVG(unitprice) > 100;