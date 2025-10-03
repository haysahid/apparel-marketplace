interface UserPointEntity {
    id: number;
    user_id: number;
    current_balance: number;
    lifetime_points: number;
    created_at: string | null;
    updated_at: string | null;
}
