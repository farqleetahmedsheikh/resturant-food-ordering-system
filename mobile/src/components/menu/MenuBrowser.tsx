import { useQuery } from "@tanstack/react-query";
import { Image } from "expo-image";
import { useMemo, useState } from "react";
import { Link } from "expo-router";
import type { Href } from "expo-router";
import {
    Pressable,
    ScrollView,
    StyleSheet,
    useWindowDimensions,
    View,
} from "react-native";
import type { DimensionValue } from "react-native";
import type { ReactNode } from "react";

import { getCategories, getMenuItems } from "@/src/api/menu.api";
import { getRestaurant } from "@/src/api/restaurant.api";
import { AppBadge } from "@/src/components/common/AppBadge";
import { AppButton } from "@/src/components/common/AppButton";
import { AppCard } from "@/src/components/common/AppCard";
import { AppText } from "@/src/components/common/AppText";
import { PriceText } from "@/src/components/common/PriceText";
import { SectionTitle } from "@/src/components/common/SectionTitle";
import { AppInput } from "@/src/components/forms/AppInput";
import { EmptyState } from "@/src/components/feedback/EmptyState";
import { ErrorState } from "@/src/components/feedback/ErrorState";
import { FeedbackMessage } from "@/src/components/feedback/FeedbackMessage";
import { LoadingState } from "@/src/components/feedback/LoadingState";
import { AppScreen } from "@/src/components/layout/AppScreen";
import { MenuItemCard } from "@/src/components/menu/MenuItemCard";
import { queryKeys } from "@/src/constants/queryKeys";
import { useCartStore } from "@/src/store/cart.store";
import { colors, radius, shadows, spacing } from "@/src/theme";
import type { MenuItem } from "@/src/types/menu";
import { getRestaurantAvailability } from "@/src/utils/restaurant";

type MenuBrowserProps = {
    mode: "public" | "customer";
};

export function MenuBrowser({ mode }: MenuBrowserProps) {
    const { width } = useWindowDimensions();
    const [category, setCategory] = useState<string | number | undefined>();
    const [search, setSearch] = useState("");
    const [feedback, setFeedback] = useState<string | null>(null);
    const addItem = useCartStore((state) => state.addItem);
    const cartCount = useCartStore((state) => state.getItemCount());

    const params = useMemo(
        () => ({
            include_unavailable: true,
            per_page: 50,
            category,
            search: search.trim() || undefined,
        }),
        [category, search],
    );

    const restaurantQuery = useQuery({
        queryKey: queryKeys.restaurant,
        queryFn: getRestaurant,
    });
    const categoriesQuery = useQuery({
        queryKey: queryKeys.categories,
        queryFn: getCategories,
    });
    const menuQuery = useQuery({
        queryKey: queryKeys.menuItems(params),
        queryFn: () => getMenuItems(params),
    });

    const restaurant = restaurantQuery.data;
    const availability = getRestaurantAvailability(restaurant);
    const isLoading =
        restaurantQuery.isLoading ||
        categoriesQuery.isLoading ||
        menuQuery.isLoading;
    const isError =
        restaurantQuery.isError || categoriesQuery.isError || menuQuery.isError;
    const refreshing =
        restaurantQuery.isRefetching ||
        categoriesQuery.isRefetching ||
        menuQuery.isRefetching;
    const items = menuQuery.data?.items ?? [];
    const categories = categoriesQuery.data ?? [];
    const addDisabled = mode === "customer" && !availability.isOpenForOrders;
    const selectedCategory = categories.find((item) => item.id === category);
    const visibleCategories = categories.filter((item) => item.is_active);
    const isWide = width >= 820;
    const isCompact = width < 680;
    const featuredItems = items.filter(
        (item) => item.is_featured && item.is_available,
    );
    const popularItems = (
        featuredItems.length > 0
            ? featuredItems
            : items.filter((item) => item.is_available)
    ).slice(0, isCompact ? 3 : 6);
    const availableCount = items.filter((item) => item.is_available).length;
    const hasFilters = Boolean(category || search.trim());
    const cardWidth: DimensionValue =
        width >= 980 ? "31.8%" : width >= 680 ? "48.4%" : "100%";
    const popularCardWidth: DimensionValue = isCompact
        ? "100%"
        : Math.min(330, Math.max(264, width * 0.78));
    const title =
        mode === "customer"
            ? "Choose your favourites"
            : "Fresh kebabs, plates & drinks";
    const subtitle =
        mode === "customer"
            ? "Build your order in a few taps and checkout securely."
            : "Browse the live Arcade Kebab House menu before signing in.";

    function retry() {
        void restaurantQuery.refetch();
        void categoriesQuery.refetch();
        void menuQuery.refetch();
    }

    function refresh() {
        retry();
    }

    function handleAdd(item: MenuItem) {
        addItem({ item, quantity: 1 });
        setFeedback(`${item.name} added to cart.`);
    }

    function clearFilters() {
        setCategory(undefined);
        setSearch("");
    }

    return (
        <AppScreen
            refreshing={refreshing}
            onRefresh={refresh}
            contentStyle={[
                styles.screen,
                mode === "customer" && styles.screenWithCartDock,
            ]}
        >
            <View style={[styles.hero, isWide && styles.heroWide]}>
                <View style={styles.heroGlow} />
                <View style={styles.heroCopy}>
                    <View style={styles.badgeRow}>
                        <AppBadge
                            label={availability.label}
                            tone={
                                availability.isOpenForOrders ? "green" : "gold"
                            }
                        />
                        <AppBadge
                            label={`${availableCount} available`}
                            tone="neutral"
                        />
                    </View>

                    <AppText variant="h1" style={styles.heroTitle}>
                        {title}
                    </AppText>
                    <AppText color={colors.text.secondary}>{subtitle}</AppText>

                    <View style={styles.heroActions}>
                        {mode === "customer" ? (
                            <Link
                                href={
                                    cartCount > 0
                                        ? "/(customer)/(tabs)/cart"
                                        : "/(customer)/(tabs)/menu"
                                }
                                asChild
                            >
                                <AppButton
                                    label={
                                        cartCount > 0
                                            ? `View cart (${cartCount})`
                                            : "Start ordering"
                                    }
                                    variant="primary"
                                />
                            </Link>
                        ) : (
                            <Link href="/(auth)/login" asChild>
                                <AppButton
                                    label="Sign in to order"
                                    variant="primary"
                                />
                            </Link>
                        )}
                    </View>
                </View>

                <View style={styles.heroStats}>
                    <InfoPill
                        label="Delivery"
                        value={
                            <PriceText amount={restaurant?.delivery_fee ?? 0} />
                        }
                    />
                    <InfoPill
                        label="Minimum"
                        value={
                            <PriceText
                                amount={restaurant?.minimum_order_amount ?? 0}
                            />
                        }
                    />
                    <InfoPill label="Items" value={`${availableCount} live`} />
                </View>
            </View>

            {mode === "customer" && !availability.isOpenForOrders ? (
                <FeedbackMessage
                    tone="warning"
                    message={
                        availability.reason ??
                        "Ordering is paused right now. You can still browse the menu."
                    }
                />
            ) : null}

            {feedback ? (
                <FeedbackMessage tone="success" message={feedback} />
            ) : null}

            {!isLoading && !isError && popularItems.length > 0 ? (
                <View style={styles.popularSection}>
                    <View style={styles.sectionHeader}>
                        <SectionTitle
                            title="Popular picks"
                            subtitle="Customer favourites ready to add."
                        />
                        {hasFilters ? (
                            <Pressable
                                accessibilityRole="button"
                                onPress={clearFilters}
                                style={({ pressed }) => [
                                    styles.clearButton,
                                    pressed && styles.pressed,
                                ]}
                            >
                                <AppText
                                    variant="caption"
                                    color={colors.brand.primary}
                                >
                                    View all
                                </AppText>
                            </Pressable>
                        ) : null}
                    </View>

                    {isCompact ? (
                        <View style={styles.popularStack}>
                            {popularItems.map((item) => (
                                <PopularItemCard
                                    key={item.id}
                                    item={item}
                                    mode={mode}
                                    width="100%"
                                    addDisabled={addDisabled}
                                    disabledReason={availability.label}
                                    onAdd={handleAdd}
                                />
                            ))}
                        </View>
                    ) : (
                        <ScrollView
                            horizontal
                            showsHorizontalScrollIndicator={false}
                            contentContainerStyle={styles.popularScroller}
                            decelerationRate="fast"
                        >
                            {popularItems.map((item) => (
                            <PopularItemCard
                                key={item.id}
                                item={item}
                                mode={mode}
                                width={popularCardWidth}
                                addDisabled={addDisabled}
                                disabledReason={availability.label}
                                onAdd={handleAdd}
                            />
                            ))}
                        </ScrollView>
                    )}
                </View>
            ) : null}

            <AppCard style={[styles.controls, isCompact && styles.controlsCompact]}>
                <View style={styles.controlsHeader}>
                    <View style={styles.controlsTitle}>
                        <AppText variant="title">Find food fast</AppText>
                        <AppText color={colors.text.secondary}>
                            Search by name or category.
                        </AppText>
                    </View>
                    {hasFilters ? (
                        <Pressable
                            accessibilityRole="button"
                            onPress={clearFilters}
                            style={({ pressed }) => [
                                styles.clearButton,
                                pressed && styles.pressed,
                            ]}
                        >
                            <AppText
                                variant="caption"
                                color={colors.brand.primary}
                            >
                                Clear
                            </AppText>
                        </Pressable>
                    ) : null}
                </View>

                <AppInput
                    label="Search menu"
                    placeholder="Search kebabs, drinks, sides…"
                    value={search}
                    onChangeText={setSearch}
                    autoCorrect={false}
                    returnKeyType="search"
                />

                <ScrollView
                    horizontal
                    showsHorizontalScrollIndicator={false}
                    contentContainerStyle={styles.filters}
                    keyboardShouldPersistTaps="handled"
                >
                    <FilterChip
                        label="All"
                        count={categories.length}
                        selected={!category}
                        onPress={() => setCategory(undefined)}
                    />
                    {visibleCategories.map((item) => (
                        <FilterChip
                            key={item.id}
                            label={item.name}
                            count={item.menu_items_count}
                            selected={category === item.id}
                            onPress={() => setCategory(item.id)}
                        />
                    ))}
                </ScrollView>
            </AppCard>

            {!isCompact ? (
                <View style={styles.infoGrid}>
                    <InfoTile
                        label="Delivery fee"
                        value={<PriceText amount={restaurant?.delivery_fee ?? 0} />}
                    />
                    <InfoTile
                        label="Category"
                        value={selectedCategory?.name ?? "All items"}
                    />
                    <InfoTile label="Results" value={`${items.length} found`} />
                </View>
            ) : null}

            {isLoading ? <LoadingState label="Loading menu..." /> : null}

            {isError ? (
                <ErrorState
                    message="We couldn’t load the menu. Try again."
                    onRetry={retry}
                />
            ) : null}

            {!isLoading && !isError ? (
                <View style={styles.list}>
                    <View style={styles.sectionHeader}>
                        <SectionTitle
                            title={selectedCategory?.name ?? "Full menu"}
                            subtitle={`${items.length} item${items.length === 1 ? "" : "s"}${search.trim() ? ` for “${search.trim()}”` : ""}`}
                        />
                        {mode === "public" ? (
                            <Link href="/(auth)/register">
                                <AppText
                                    variant="caption"
                                    color={colors.brand.primary}
                                >
                                    Create account
                                </AppText>
                            </Link>
                        ) : null}
                    </View>

                    {items.length === 0 ? (
                        <EmptyState
                            title="No items found"
                            message={
                                hasFilters
                                    ? "Try another category or clear your search."
                                    : "The menu is being updated. Please check again soon."
                            }
                        />
                    ) : (
                        <View style={styles.menuGrid}>
                            {items.map((item) => (
                                <View
                                    key={item.id}
                                    style={[
                                        styles.cardCell,
                                        { width: cardWidth },
                                    ]}
                                >
                                    <MenuItemCard
                                        item={item}
                                        variant={isCompact ? "compact" : "default"}
                                        detailHref={
                                            mode === "public"
                                                ? (`/(public)/item/${item.id}` as Href)
                                                : undefined
                                        }
                                        showAddButton={mode === "customer"}
                                        addDisabled={addDisabled}
                                        addDisabledReason={
                                            addDisabled
                                                ? availability.label
                                                : undefined
                                        }
                                        onAdd={handleAdd}
                                    />
                                </View>
                            ))}
                        </View>
                    )}
                </View>
            ) : null}

            {mode === "customer" ? (
                <View style={styles.cartDock}>
                    <View style={styles.cartDockCopy}>
                        <AppText
                            variant="caption"
                            color={colors.text.secondary}
                        >
                            {cartCount > 0
                                ? `${cartCount} item${cartCount === 1 ? "" : "s"} in cart`
                                : "Ready to order?"}
                        </AppText>
                        <AppText variant="title">
                            {cartCount > 0
                                ? "Review your order"
                                : "Start with a favourite"}
                        </AppText>
                    </View>
                    <Link
                        href={
                            cartCount > 0
                                ? "/(customer)/(tabs)/cart"
                                : "/(customer)/(tabs)/menu"
                        }
                        asChild
                    >
                        <AppButton
                            label={cartCount > 0 ? "View cart" : "Start"}
                            variant="primary"
                        />
                    </Link>
                </View>
            ) : null}
        </AppScreen>
    );
}

type FilterChipProps = {
    label: string;
    count?: number;
    selected: boolean;
    onPress: () => void;
};

function FilterChip({ label, count, selected, onPress }: FilterChipProps) {
    return (
        <Pressable
            accessibilityRole="button"
            accessibilityState={{ selected }}
            onPress={onPress}
            style={({ pressed }) => [
                styles.chip,
                selected && styles.selectedChip,
                pressed && styles.pressed,
            ]}
        >
            <AppText
                variant="caption"
                color={selected ? colors.text.inverse : colors.text.secondary}
                numberOfLines={1}
            >
                {label}
            </AppText>
            {typeof count === "number" ? (
                <View
                    style={[
                        styles.chipCount,
                        selected && styles.selectedChipCount,
                    ]}
                >
                    <AppText
                        variant="caption"
                        color={
                            selected
                                ? colors.brand.primary
                                : colors.text.secondary
                        }
                    >
                        {count}
                    </AppText>
                </View>
            ) : null}
        </Pressable>
    );
}

type PopularItemCardProps = {
    item: MenuItem;
    mode: "public" | "customer";
    width: DimensionValue;
    addDisabled: boolean;
    disabledReason?: string;
    onAdd: (item: MenuItem) => void;
};

function PopularItemCard({
    item,
    mode,
    width,
    addDisabled,
    disabledReason,
    onAdd,
}: PopularItemCardProps) {
    const detailHref = `/(public)/item/${item.id}` as Href;
    const description =
        item.description ?? "Freshly prepared and packed with flavour.";

    return (
        <AppCard style={[styles.popularCard, { width }]}>
            <View style={styles.popularTopRow}>
                <View style={styles.popularImageFallback}>
                    {item.image_url ? (
                        <Image
                            source={{ uri: item.image_url }}
                            style={styles.popularImage}
                            contentFit="cover"
                            accessibilityRole="image"
                            accessibilityLabel={`${item.name} photo`}
                        />
                    ) : (
                        <AppText variant="h1" color={colors.brand.primary}>
                            {item.name.charAt(0).toUpperCase()}
                        </AppText>
                    )}
                </View>
                <View style={styles.popularMeta}>
                    <View style={styles.badgeRow}>
                        {item.is_featured ? (
                            <AppBadge label="Popular" tone="gold" />
                        ) : null}
                        <AppBadge
                            label={
                                item.is_available ? "Available" : "Unavailable"
                            }
                            tone={item.is_available ? "green" : "neutral"}
                        />
                    </View>
                    <AppText variant="title" numberOfLines={2}>
                        {item.name}
                    </AppText>
                    <AppText color={colors.text.secondary} numberOfLines={2}>
                        {description}
                    </AppText>
                </View>
            </View>

            <View style={styles.popularBottomRow}>
                <PriceText amount={item.price ?? 0} />
                {mode === "public" ? (
                    <Link href={detailHref} asChild>
                        <Pressable
                            accessibilityRole="button"
                            style={({ pressed }) => [
                                styles.smallPrimaryButton,
                                pressed && styles.pressed,
                            ]}
                        >
                            <AppText
                                variant="caption"
                                color={colors.text.inverse}
                            >
                                View
                            </AppText>
                        </Pressable>
                    </Link>
                ) : (
                    <Pressable
                        accessibilityRole="button"
                        accessibilityLabel={`Add ${item.name} to cart`}
                        disabled={addDisabled || !item.is_available}
                        onPress={() => onAdd(item)}
                        style={({ pressed }) => [
                            styles.smallPrimaryButton,
                            (addDisabled || !item.is_available) &&
                                styles.disabledButton,
                            pressed && styles.pressed,
                        ]}
                    >
                        <AppText variant="caption" color={colors.text.inverse}>
                            {addDisabled
                                ? (disabledReason ?? "Closed")
                                : item.is_available
                                  ? "Add"
                                  : "Sold out"}
                        </AppText>
                    </Pressable>
                )}
            </View>
        </AppCard>
    );
}

type InfoPillProps = {
    label: string;
    value: string | ReactNode;
};

function InfoPill({ label, value }: InfoPillProps) {
    return (
        <View style={styles.infoPill}>
            <AppText variant="caption" color={colors.text.secondary}>
                {label}
            </AppText>
            {typeof value === "string" ? (
                <AppText variant="title">{value}</AppText>
            ) : (
                value
            )}
        </View>
    );
}

type InfoTileProps = {
    label: string;
    value: string | ReactNode;
};

function InfoTile({ label, value }: InfoTileProps) {
    return (
        <AppCard style={styles.infoTile}>
            <AppText variant="caption" color={colors.text.secondary}>
                {label}
            </AppText>
            {typeof value === "string" ? (
                <AppText variant="title" numberOfLines={1}>
                    {value}
                </AppText>
            ) : (
                value
            )}
        </AppCard>
    );
}

const styles = StyleSheet.create({
    screen: {
        gap: spacing.md,
        alignSelf: "center",
        width: "100%",
        maxWidth: 1120,
    },
    screenWithCartDock: {
        paddingBottom: 110,
    },
    mobileHeader: {
        flexDirection: "row",
        alignItems: "center",
        justifyContent: "space-between",
        gap: spacing.md,
    },
    brandRow: {
        minWidth: 0,
        flex: 1,
        flexDirection: "row",
        alignItems: "center",
        gap: spacing.md,
    },
    brandCopy: {
        flex: 1,
        gap: 2,
    },
    cartPill: {
        minHeight: 44,
        flexDirection: "row",
        alignItems: "center",
        gap: spacing.sm,
        borderRadius: radius.pill,
        borderWidth: 1,
        borderColor: colors.brand.primary,
        backgroundColor: colors.brand.primary,
        paddingHorizontal: spacing.md,
    },
    signInPill: {
        minHeight: 44,
        justifyContent: "center",
        borderRadius: radius.pill,
        borderWidth: 1,
        borderColor: colors.border.strong,
        backgroundColor: colors.surface.card,
        paddingHorizontal: spacing.lg,
    },
    cartCount: {
        minWidth: 24,
        alignItems: "center",
        justifyContent: "center",
        borderRadius: radius.pill,
        backgroundColor: colors.surface.muted,
        paddingHorizontal: spacing.xs,
        paddingVertical: 2,
    },
    activeCartCount: {
        backgroundColor: colors.surface.card,
    },
    hero: {
        position: "relative",
        overflow: "hidden",
        gap: spacing.md,
        borderRadius: 26,
        borderWidth: 1,
        borderColor: colors.border.light,
        backgroundColor: colors.surface.card,
        padding: spacing.lg,
        ...shadows.card,
    },
    heroWide: {
        flexDirection: "row",
        alignItems: "stretch",
        justifyContent: "space-between",
    },
    heroGlow: {
        position: "absolute",
        top: -86,
        right: -78,
        height: 180,
        width: 180,
        borderRadius: 999,
        backgroundColor: colors.brand.soft,
    },
    heroCopy: {
        flex: 1,
        minWidth: 0,
        maxWidth: 640,
        gap: spacing.md,
    },
    heroTitle: {
        fontSize: 28,
        lineHeight: 34,
    },
    badgeRow: {
        flexDirection: "row",
        flexWrap: "wrap",
        gap: spacing.sm,
    },
    heroActions: {
        flexDirection: "row",
        flexWrap: "wrap",
        gap: spacing.sm,
        paddingTop: spacing.xs,
    },
    heroStats: {
        minWidth: 220,
        flexDirection: "row",
        flexWrap: "wrap",
        gap: spacing.xs,
    },
    infoPill: {
        minWidth: 94,
        flex: 1,
        gap: 2,
        justifyContent: "center",
        borderRadius: radius.lg,
        borderWidth: 1,
        borderColor: colors.border.light,
        backgroundColor: colors.surface.muted,
        paddingHorizontal: spacing.md,
        paddingVertical: spacing.sm,
    },
    popularSection: {
        gap: spacing.md,
    },
    popularStack: {
        gap: spacing.sm,
    },
    popularScroller: {
        gap: spacing.md,
        paddingRight: spacing.md,
    },
    popularCard: {
        gap: spacing.sm,
        padding: spacing.md,
    },
    popularTopRow: {
        flexDirection: "row",
        gap: spacing.md,
    },
    popularImageFallback: {
        height: 78,
        width: 78,
        flexShrink: 0,
        alignItems: "center",
        justifyContent: "center",
        overflow: "hidden",
        borderRadius: radius.lg,
        backgroundColor: colors.brand.soft,
    },
    popularImage: {
        height: "100%",
        width: "100%",
    },
    popularMeta: {
        minWidth: 0,
        flex: 1,
        gap: spacing.xs,
    },
    popularBottomRow: {
        flexDirection: "row",
        alignItems: "center",
        justifyContent: "space-between",
        gap: spacing.md,
    },
    smallPrimaryButton: {
        minHeight: 42,
        minWidth: 82,
        alignItems: "center",
        justifyContent: "center",
        borderRadius: radius.pill,
        backgroundColor: colors.brand.primary,
        paddingHorizontal: spacing.lg,
    },
    disabledButton: {
        backgroundColor: colors.text.secondary,
    },
    controls: {
        gap: spacing.md,
    },
    controlsCompact: {
        padding: spacing.md,
    },
    controlsHeader: {
        flexDirection: "row",
        alignItems: "flex-start",
        justifyContent: "space-between",
        gap: spacing.md,
    },
    controlsTitle: {
        flex: 1,
        gap: spacing.xs,
    },
    clearButton: {
        minHeight: 40,
        justifyContent: "center",
        borderRadius: radius.pill,
        backgroundColor: colors.brand.soft,
        paddingHorizontal: spacing.lg,
    },
    filters: {
        gap: spacing.sm,
        paddingRight: spacing.md,
    },
    chip: {
        minHeight: 44,
        flexDirection: "row",
        alignItems: "center",
        justifyContent: "center",
        gap: spacing.sm,
        borderRadius: radius.pill,
        borderWidth: 1,
        borderColor: colors.border.strong,
        backgroundColor: colors.surface.card,
        paddingHorizontal: spacing.lg,
    },
    selectedChip: {
        borderColor: colors.brand.primary,
        backgroundColor: colors.brand.primary,
    },
    chipCount: {
        minWidth: 26,
        alignItems: "center",
        borderRadius: radius.pill,
        backgroundColor: colors.surface.muted,
        paddingHorizontal: spacing.sm,
        paddingVertical: 2,
    },
    selectedChipCount: {
        backgroundColor: colors.surface.card,
    },
    infoGrid: {
        flexDirection: "row",
        flexWrap: "wrap",
        gap: spacing.sm,
    },
    infoTile: {
        minWidth: 150,
        flex: 1,
        gap: spacing.xs,
    },
    list: {
        gap: spacing.md,
    },
    sectionHeader: {
        flexDirection: "row",
        alignItems: "flex-end",
        justifyContent: "space-between",
        gap: spacing.md,
    },
    menuGrid: {
        flexDirection: "row",
        flexWrap: "wrap",
        gap: spacing.md,
    },
    cardCell: {
        minWidth: 0,
    },
    cartDock: {
        flexDirection: "row",
        alignItems: "center",
        justifyContent: "space-between",
        gap: spacing.md,
        borderRadius: 28,
        borderWidth: 1,
        borderColor: colors.border.strong,
        backgroundColor: colors.surface.card,
        padding: spacing.md,
        ...shadows.card,
    },
    cartDockCopy: {
        minWidth: 0,
        flex: 1,
        gap: 2,
    },
    pressed: {
        transform: [{ scale: 0.98 }],
        opacity: 0.9,
    },
});
