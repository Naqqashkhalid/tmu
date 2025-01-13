-- //////////////////////////
-- Update Person's Total Dramas

UPDATE wp_tmu_people p
LEFT JOIN (
  SELECT person, COUNT(DISTINCT dramas) AS unique_dramas
  FROM wp_tmu_dramas_cast
  GROUP BY person
) AS dc
ON p.ID = dc.person
SET p.no_dramas = COALESCE(dc.unique_dramas, 0);


-- //////////////////////////
-- Update Person's Total Movies

UPDATE wp_tmu_people p
LEFT JOIN (
  SELECT person, COUNT(DISTINCT movie) AS unique_movies
  FROM wp_tmu_movies_cast
  GROUP BY person
) AS dc
ON p.ID = dc.person
SET p.no_movies = COALESCE(dc.unique_movies, 0);

-- //////////////////////////
-- Update Person's Total TV Series

UPDATE wp_tmu_people p
LEFT JOIN (
  SELECT person, COUNT(DISTINCT tv_series) AS unique_tv_series
  FROM wp_tmu_tv_series_cast
  GROUP BY person
) AS dc
ON p.ID = dc.person
SET p.no_tv_series = COALESCE(dc.unique_tv_series, 0);
