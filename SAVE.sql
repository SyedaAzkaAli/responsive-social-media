/* ==============================================================
   STORIES – 10 fresh rows (uploaded within the last 24 hours)
   ============================================================== */

INSERT INTO stories (user_id, image_url, created_at)
VALUES
  ((SELECT user_id FROM users WHERE username = 'lrose'   ), 'images/story-1.jpg', NOW() - INTERVAL ' 2 HOURS'),
  ((SELECT user_id FROM users WHERE username = 'bdwayne' ), 'images/story-2.jpg', NOW() - INTERVAL ' 3 HOURS'),
  ((SELECT user_id FROM users WHERE username = 'mberry'  ), 'images/story-3.jpg', NOW() - INTERVAL ' 5 HOURS'),
  ((SELECT user_id FROM users WHERE username = 'cbrown'  ), 'images/story-4.jpg', NOW() - INTERVAL ' 6 HOURS'),
  ((SELECT user_id FROM users WHERE username = 'ajackie' ), 'images/story-5.jpg', NOW() - INTERVAL ' 7 HOURS'),
  ((SELECT user_id FROM users WHERE username = 'janedoe' ), 'images/story-6.jpg', NOW() - INTERVAL ' 8 HOURS');
 


/* ==============================================================
   FRIEND REQUESTS – 8 pending rows sent to user_id = 1 (Diana)
   ============================================================== */

INSERT INTO friend_requests (sender_id, receiver_id, status, created_at)
VALUES
  ((SELECT user_id FROM users WHERE username = 'janedoe' ), 1, 'pending', NOW() - INTERVAL '30 MINUTES'),
  ((SELECT user_id FROM users WHERE username = 'kbenjamin'), 1, 'pending', NOW() - INTERVAL ' 1 HOUR'),
  ((SELECT user_id FROM users WHERE username = 'moppong'  ), 1, 'pending', NOW() - INTERVAL ' 2 HOURS'),
  ((SELECT user_id FROM users WHERE username = 'dlartey'  ), 1, 'pending', NOW() - INTERVAL ' 3 HOURS'),
  ((SELECT user_id FROM users WHERE username = 'djackson' ), 1, 'pending', NOW() - INTERVAL ' 6 HOURS'),
  ((SELECT user_id FROM users WHERE username = 'fdeila'   ), 1, 'pending', NOW() - INTERVAL ' 8 HOURS'),
  ((SELECT user_id FROM users WHERE username = 'equist'   ), 1, 'pending', NOW() - INTERVAL '10 HOURS'),
  ((SELECT user_id FROM users WHERE username = 'bdwayne'  ), 1, 'pending', NOW() - INTERVAL '12 HOURS');