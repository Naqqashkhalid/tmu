-- Drama Episdoes Total Vote Count
UPDATE wp_tmu_dramas_episodes AS e
LEFT JOIN (
  SELECT comment_post_ID, COUNT(*) AS total_comment_votes
  FROM wp_comments
  WHERE comment_rating > 0
  GROUP BY comment_post_ID
) AS c ON e.ID = c.comment_post_ID
SET e.total_vote_count = COALESCE(e.vote_count, 0) + COALESCE(c.total_comment_votes, 0);


-- Drama Episdoes Total Average Rating
UPDATE wp_tmu_dramas_episodes AS e
LEFT JOIN (
  SELECT comment_post_ID, ROUND(AVG(comment_rating), 1) AS average_comment_rating
  FROM wp_comments
  WHERE comment_rating > 0
  GROUP BY comment_post_ID
) AS c ON e.ID = c.comment_post_ID
SET e.total_average_rating = CASE 
  WHEN e.average_rating > 0 AND c.average_comment_rating > 0 THEN (e.average_rating + c.average_comment_rating) / 2
  WHEN e.average_rating > 0 THEN e.average_rating
  WHEN c.average_comment_rating > 0 THEN c.average_comment_rating
  ELSE e.average_rating
END;



-- Dramas Total Average Rating
-- //////////////////////////////////
UPDATE wp_tmu_dramas AS d
  LEFT JOIN (
    /* Calculate average episode rating */
    SELECT dramas,ROUND(AVG(total_average_rating), 1) AS average_episodes_rating
    FROM wp_tmu_dramas_episodes WHERE total_average_rating > 0
    GROUP BY dramas
  ) AS e ON d.ID = e.dramas
  LEFT JOIN (
    /* Calculate average comment rating */
    SELECT comment_post_ID,ROUND(AVG(comment_rating), 1) AS average_comment_rating
    FROM wp_comments WHERE comment_rating > 0
    GROUP BY comment_post_ID
  ) AS c ON d.ID = c.comment_post_ID
SET d.total_average_rating = ROUND(CASE
  WHEN e.average_episodes_rating > 0 AND c.average_comment_rating > 0 AND d.average_rating > 0 THEN 
    (e.average_episodes_rating + c.average_comment_rating + d.average_rating) / 3
  WHEN e.average_episodes_rating > 0 AND c.average_comment_rating > 0 THEN 
    (e.average_episodes_rating + c.average_comment_rating) / 2
  WHEN e.average_episodes_rating > 0 AND d.average_rating > 0 THEN 
    (e.average_episodes_rating + d.average_rating) / 2
  WHEN c.average_comment_rating > 0 AND d.average_rating > 0 THEN 
    (c.average_comment_rating + d.average_rating) / 2
  WHEN e.average_episodes_rating > 0 THEN 
    e.average_episodes_rating
  WHEN c.average_comment_rating > 0 THEN 
    c.average_comment_rating
  ELSE
    d.average_rating
END, 1);
-- ///////////////////////////



-- Dramas Total Vote Count
UPDATE wp_tmu_dramas AS d
LEFT JOIN (
  SELECT dramas, SUM(COALESCE(total_vote_count, 0)) AS total_episodes_votes
  FROM wp_tmu_dramas_episodes
  GROUP BY dramas
) AS e ON d.ID = e.dramas
LEFT JOIN (
  SELECT comment_post_ID, COALESCE(COUNT(*), 0) AS total_comment_votes
  FROM wp_comments
  WHERE comment_rating > 0
  GROUP BY comment_post_ID
) AS c ON d.ID = c.comment_post_ID
SET d.total_vote_count = COALESCE(d.vote_count, 0) + COALESCE(e.total_episodes_votes, 0) + COALESCE(c.total_comment_votes, 0);


-- TV Series
-- ////////////////////////////////////////////////////////////////////

-- TV Series Episdoes Total Vote Count
UPDATE wp_tmu_tv_series_episodes AS e
LEFT JOIN (
  SELECT comment_post_ID, COUNT(*) AS total_comment_votes
  FROM wp_comments
  WHERE comment_rating > 0
  GROUP BY comment_post_ID
) AS c ON e.ID = c.comment_post_ID
SET e.total_vote_count = COALESCE(e.vote_count, 0) + COALESCE(c.total_comment_votes, 0);


-- TV Series Episdoes Total Average Rating
UPDATE wp_tmu_tv_series_episodes AS e
LEFT JOIN (
  SELECT comment_post_ID, ROUND(AVG(comment_rating), 1) AS average_comment_rating
  FROM wp_comments
  WHERE comment_rating > 0
  GROUP BY comment_post_ID
) AS c ON e.ID = c.comment_post_ID
SET e.total_average_rating = CASE 
  WHEN e.average_rating > 0 AND c.average_comment_rating > 0 THEN (e.average_rating + c.average_comment_rating) / 2
  WHEN e.average_rating > 0 THEN e.average_rating
  WHEN c.average_comment_rating > 0 THEN c.average_comment_rating
  ELSE e.average_rating
END;



-- TV Series Total Average Rating
-- //////////////////////////////////
UPDATE wp_tmu_tv_series AS d
  LEFT JOIN (
    /* Calculate average episode rating */
    SELECT tv_series,ROUND(AVG(total_average_rating), 1) AS average_episodes_rating
    FROM wp_tmu_tv_series_episodes WHERE total_average_rating > 0
    GROUP BY tv_series
  ) AS e ON d.ID = e.tv_series
  LEFT JOIN (
    /* Calculate average comment rating */
    SELECT comment_post_ID,ROUND(AVG(comment_rating), 1) AS average_comment_rating
    FROM wp_comments WHERE comment_rating > 0
    GROUP BY comment_post_ID
  ) AS c ON d.ID = c.comment_post_ID
SET d.total_average_rating = ROUND(CASE
  WHEN e.average_episodes_rating > 0 AND c.average_comment_rating > 0 AND d.average_rating > 0 THEN 
    (e.average_episodes_rating + c.average_comment_rating + d.average_rating) / 3
  WHEN e.average_episodes_rating > 0 AND c.average_comment_rating > 0 THEN 
    (e.average_episodes_rating + c.average_comment_rating) / 2
  WHEN e.average_episodes_rating > 0 AND d.average_rating > 0 THEN 
    (e.average_episodes_rating + d.average_rating) / 2
  WHEN c.average_comment_rating > 0 AND d.average_rating > 0 THEN 
    (c.average_comment_rating + d.average_rating) / 2
  WHEN e.average_episodes_rating > 0 THEN 
    e.average_episodes_rating
  WHEN c.average_comment_rating > 0 THEN 
    c.average_comment_rating
  ELSE
    d.average_rating
END, 1);
-- ///////////////////////////



-- TV Series Total Vote Count
UPDATE wp_tmu_tv_series AS d
LEFT JOIN (
  SELECT tv_series, SUM(COALESCE(total_vote_count, 0)) AS total_episodes_votes
  FROM wp_tmu_tv_series_episodes
  GROUP BY tv_series
) AS e ON d.ID = e.tv_series
LEFT JOIN (
  SELECT comment_post_ID, COALESCE(COUNT(*), 0) AS total_comment_votes
  FROM wp_comments
  WHERE comment_rating > 0
  GROUP BY comment_post_ID
) AS c ON d.ID = c.comment_post_ID
SET d.total_vote_count = COALESCE(d.vote_count, 0) + COALESCE(e.total_episodes_votes, 0) + COALESCE(c.total_comment_votes, 0);



-- Movies
-- ////////////////////////////////////////////////////////////////

-- Movies Episdoes Total Vote Count
UPDATE wp_tmu_movies AS e
LEFT JOIN (
  SELECT comment_post_ID, COUNT(*) AS total_comment_votes
  FROM wp_comments
  WHERE comment_rating > 0
  GROUP BY comment_post_ID
) AS c ON e.ID = c.comment_post_ID
SET e.total_vote_count = COALESCE(e.vote_count, 0) + (c.total_comment_votes, 0);


-- TV Series Episdoes Total Average Rating
UPDATE wp_tmu_movies AS e
LEFT JOIN (
  SELECT comment_post_ID, ROUND(AVG(comment_rating), 1) AS average_comment_rating
  FROM wp_comments
  WHERE comment_rating > 0
  GROUP BY comment_post_ID
) AS c ON e.ID = c.comment_post_ID
SET e.total_average_rating = CASE 
  WHEN e.average_rating > 0 AND c.average_comment_rating > 0 THEN (e.average_rating + c.average_comment_rating) / 2
  WHEN e.average_rating > 0 THEN e.average_rating
  WHEN c.average_comment_rating > 0 THEN c.average_comment_rating
  ELSE e.average_rating
END;