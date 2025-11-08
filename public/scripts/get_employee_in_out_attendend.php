<?php
$sql="SELECT 
    a.uid,
    a.date,
    DATE_FORMAT(a.date, '%Y-%m') AS month,
    DATE_FORMAT(a.date, '%Y-%m-%d') AS formatted_date,
    MIN(a.edit_status) AS in_time_edit_status,
    MAX(a.edit_status) AS out_time_edit_status,
    e.emp_name_with_initial,
    e.calling_name,
    b.location,
    d.name AS dept_name,
    st.shift_name,
    st.onduty_time AS shift_start_time,
    st.offduty_time AS shift_end_time,
    MIN(CASE 
        WHEN TIME(a.timestamp) BETWEEN 
            SUBTIME(st.onduty_time, '02:00:00') AND 
            ADDTIME(st.onduty_time, '04:00:00')
        THEN a.timestamp 
        ELSE NULL 
    END) AS firsttimestamp,
    -- CASE 
    --     WHEN COUNT(DISTINCT a.timestamp) = 1 
    --     THEN NULL
    --     ELSE MAX(CASE 
    --             WHEN TIME(a.timestamp) BETWEEN 
    --                 SUBTIME(st.offduty_time, '02:00:00') AND 
    --                 ADDTIME(st.offduty_time, '06:00:00')
    --             THEN a.timestamp 
    --             ELSE NULL 
    --         END)
    -- END AS lasttimestamp
    (CASE 
        WHEN Min(a.timestamp) = Max(a.timestamp) THEN ''  
        ELSE Max(a.timestamp)
        END) AS lasttimestamp
FROM 
    attendances a
JOIN 
    employees e ON a.emp_id = e.emp_id
JOIN 
    branches b ON e.emp_location = b.id
JOIN 
    departments d ON e.emp_department = d.id
JOIN 
    shift_types st ON e.emp_shift = st.id
WHERE 
    $where
GROUP BY 
    a.date, 
    e.emp_id, 
    e.emp_name_with_initial, 
    st.shift_name, 
    st.onduty_time, 
    st.offduty_time
ORDER BY 
    a.date, 
    e.emp_name_with_initial";
?>