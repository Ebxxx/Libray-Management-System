-- Supabase (PostgreSQL)

CREATE TABLE public.activity_logs (
  log_id integer NOT NULL DEFAULT nextval('activity_logs_log_id_seq'::regclass),
  user_id integer,
  action_type character varying NOT NULL,
  action_description text NOT NULL,
  ip_address inet,
  timestamp timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT activity_logs_pkey PRIMARY KEY (log_id),
  CONSTRAINT activity_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id)
);
CREATE TABLE public.books (
  book_id integer NOT NULL DEFAULT nextval('books_book_id_seq'::regclass),
  resource_id integer NOT NULL,
  author character varying NOT NULL,
  isbn character varying NOT NULL,
  publisher character varying NOT NULL,
  edition character varying,
  publication_date date,
  type text,
  CONSTRAINT books_pkey PRIMARY KEY (book_id),
  CONSTRAINT books_resource_id_fkey FOREIGN KEY (resource_id) REFERENCES public.library_resources(resource_id)
);
CREATE TABLE public.borrowings (
  borrowing_id integer NOT NULL DEFAULT nextval('borrowings_borrowing_id_seq'::regclass),
  user_id integer NOT NULL,
  resource_id integer NOT NULL,
  borrow_date timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
  due_date timestamp with time zone,
  return_date timestamp with time zone,
  fine_amount numeric DEFAULT 0.00,
  status USER-DEFINED DEFAULT 'pending'::borrowing_status,
  approved_by integer,
  approved_at timestamp with time zone,
  returned_by integer,
  CONSTRAINT borrowings_pkey PRIMARY KEY (borrowing_id),
  CONSTRAINT borrowings_approved_by_fkey FOREIGN KEY (approved_by) REFERENCES public.users(user_id),
  CONSTRAINT borrowings_resource_id_fkey FOREIGN KEY (resource_id) REFERENCES public.library_resources(resource_id),
  CONSTRAINT borrowings_returned_by_fkey FOREIGN KEY (returned_by) REFERENCES public.users(user_id),
  CONSTRAINT borrowings_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id)
);
CREATE TABLE public.fine_configurations (
  config_id integer NOT NULL DEFAULT nextval('fine_configurations_config_id_seq'::regclass),
  resource_type USER-DEFINED NOT NULL UNIQUE,
  fine_amount numeric NOT NULL DEFAULT 1.00,
  updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fine_configurations_pkey PRIMARY KEY (config_id)
);
CREATE TABLE public.fine_payments (
  payment_id integer NOT NULL DEFAULT nextval('fine_payments_payment_id_seq'::regclass),
  borrowing_id integer NOT NULL,
  amount_paid numeric NOT NULL,
  payment_date timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
  payment_status USER-DEFINED DEFAULT 'pending'::payment_status,
  processed_by integer,
  payment_notes text,
  cash_received numeric NOT NULL,
  change_amount real NOT NULL,
  CONSTRAINT fine_payments_pkey PRIMARY KEY (payment_id),
  CONSTRAINT fine_payments_processed_by_fkey FOREIGN KEY (processed_by) REFERENCES public.users(user_id),
  CONSTRAINT fine_payments_borrowing_id_fkey FOREIGN KEY (borrowing_id) REFERENCES public.borrowings(borrowing_id)
);
CREATE TABLE public.library_resources (
  resource_id integer NOT NULL DEFAULT nextval('library_resources_resource_id_seq'::regclass),
  title character varying NOT NULL,
  accession_number character varying NOT NULL UNIQUE,
  category text NOT NULL,
  status USER-DEFINED DEFAULT 'borrowed'::resource_status,
  created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
  cover_image character varying,
  CONSTRAINT library_resources_pkey PRIMARY KEY (resource_id)
);
CREATE TABLE public.media_resources (
  media_id integer NOT NULL DEFAULT nextval('media_resources_media_id_seq'::regclass),
  resource_id integer NOT NULL,
  format character varying NOT NULL,
  runtime integer,
  media_type character varying NOT NULL,
  type text,
  CONSTRAINT media_resources_pkey PRIMARY KEY (media_id),
  CONSTRAINT media_resources_resource_id_fkey FOREIGN KEY (resource_id) REFERENCES public.library_resources(resource_id)
);
CREATE TABLE public.periodicals (
  periodical_id integer NOT NULL DEFAULT nextval('periodicals_periodical_id_seq'::regclass),
  resource_id integer NOT NULL,
  issn character varying NOT NULL,
  volume character varying,
  issue character varying,
  publication_date date,
  type text,
  CONSTRAINT periodicals_pkey PRIMARY KEY (periodical_id),
  CONSTRAINT periodicals_resource_id_fkey FOREIGN KEY (resource_id) REFERENCES public.library_resources(resource_id)
);
CREATE TABLE public.users (
  user_id integer NOT NULL DEFAULT nextval('users_user_id_seq'::regclass),
  membership_id character varying UNIQUE,
  username character varying NOT NULL UNIQUE,
  password character varying NOT NULL,
  first_name character varying NOT NULL,
  last_name character varying NOT NULL,
  email character varying NOT NULL UNIQUE,
  role USER-DEFINED NOT NULL,
  max_books integer NOT NULL,
  created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
  borrowing_days_limit integer DEFAULT 7,
  CONSTRAINT users_pkey PRIMARY KEY (user_id)
);