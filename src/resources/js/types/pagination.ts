export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

// Laravel's length-aware paginator as serialized by Inertia.
export type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};
