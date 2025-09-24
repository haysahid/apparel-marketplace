interface PaginationModel<T> {
    data: T[];
    current_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string; label: string; active: boolean }>;
    to: number;
    from: number;
}
