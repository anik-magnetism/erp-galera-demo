# ERP Galera Demo — Overview and Runbook

This repository provides a demo Laravel backend connected to a 3-node MariaDB/Galera cluster (synchronous replication) for experimenting with large dataset seeding, read/write behavior, and node failure scenarios.

**Architecture**
- Services: `laravel_app`, `redis`, `galera1`, `galera2`, `galera3`, `phpmyadmin` (optional).
- Galera image: `docker.io/bitnami/mariadb-galera:12.0.2-debian-12-r0` (3 nodes, cluster name `erp_cluster`).
- Laravel queue uses Redis. Use `php artisan queue:work` to process seeding jobs.

## Docker startup

1. Build and start services:

```bash
docker-compose up -d --build
```

2. Confirm services are healthy:

```bash
docker-compose ps
```

3. Run migrations and schedule seeding (small sync parts + queued heavy imports):

```bash
php artisan migrate:fresh --seed
# or trigger only the large seeded jobs
php artisan db:seed --class=DatabaseSeeder
php artisan db:seed --class=LargeDatasetSeeder
```

4. Start a queue worker to process the heavy import jobs:

```bash
php artisan queue:work --queue=default --sleep=3 --tries=3
```

## Seeder strategy
- Small reference tables (companies, warehouses) are created synchronously with chunked inserts.
- Large tables (customers, products, orders, audit_logs) are inserted by queue workers in chunked batches (default 1000 rows per insert) to avoid large memory usage.
- Seed jobs use `DB::table()->insert()` for raw performance and are safe to run concurrently from multiple workers.
- Order and order_items are created together in job batches to keep referential integrity local to the job.

## How to run the large seeder

- Ensure you have the queue worker running (`php artisan queue:work`).
- Dispatch the seeding jobs:

```bash
php artisan db:seed --class=LargeDatasetSeeder
```

The seeder will dispatch jobs for:
- 50,000 customers
- 10,000 products
- 500,000 orders (with order_items)
- 1,000,000 audit logs

Note: These numbers are configurable in the job constructors.

## Galera behavior and monitoring

Useful status queries (exec into any Galera container where `mysql` client exists):

```bash
docker exec -it galera1 mysql -uroot -prootpassword -e "SHOW STATUS LIKE 'wsrep_cluster_size';"
docker exec -it galera1 mysql -uroot -prootpassword -e "SHOW STATUS LIKE 'wsrep_local_state_comment';"
docker exec -it galera1 mysql -uroot -prootpassword -e "SHOW STATUS LIKE 'wsrep_ready';"
```

These show cluster size, local node state (Synced/Joining/Donor/Desynced), and readiness.

## Simulate node failure

1. Stop a node (e.g., `galera3`):

```bash
docker stop galera3
```

2. Check cluster size should drop to 2:

```bash
docker exec -it galera1 mysql -uroot -prootpassword -e "SHOW STATUS LIKE 'wsrep_cluster_size';"
```

3. Restart the node:

```bash
docker start galera3
```

4. The node will attempt automatic state transfer (IST/SST) and rejoin the cluster; check `wsrep_local_state_comment` and `wsrep_ready` for progress.

## Optimization notes
- Add indexes on all foreign keys and composite `(company_id, created_at)` to speed common queries (already applied in migrations).
- Use chunked inserts (`chunk(1000)`) and queued jobs for heavy imports.
- Cache dashboard metrics with Redis to avoid heavy aggregate queries.
- Enable slow query logging in MySQL/MariaDB for queries exceeding a threshold and tune indexes accordingly.
- Consider partitioning large tables (e.g., `orders`, `audit_logs`) by year or range to speed archival and pruning.

## Commands quick reference

- Start environment: `docker-compose up -d --build`
- Run migrations and seed: `php artisan migrate:fresh --seed`
- Dispatch large seeder: `php artisan db:seed --class=LargeDatasetSeeder`
- Run queue worker: `php artisan queue:work`
- Stress test command: `php artisan demo:stress-test --count=10000 --concurrency=50`

## Scaling to production
- Use dedicated load balancers and separate app / queue workers horizontally.
- Use managed Galera clusters with appropriate SST/IST methods and backup strategies.
- Monitor replication lag, flow-control (`wsrep_flow_control_paused`), and tune Galera settings for write-heavy workloads.
