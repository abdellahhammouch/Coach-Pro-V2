------ Challenge 2:


------ 1:
SELECT u.nom, u.prenom, r.id, r.reserved_at, COUNT(r.id) as total FROM reservations r
LEFT JOIN users u ON r.sportif_id = u.id
WHERE DATE_FORMAT(reserved_at, '%m') = 01
GROUP BY r.id
ORDER BY reserved_at;



------ 2:
SELECT r.sportif_id,
COUNT(*) as nbr_res_parMois,
COUNT(r.id) as total_reservation
FROM reservations r
LEFT JOIN users u ON r.sportif_id = u.id
WHERE DATE_FORMAT(reserved_at, '%m') = 01
GROUP BY r.sportif_id;



------ 3:
