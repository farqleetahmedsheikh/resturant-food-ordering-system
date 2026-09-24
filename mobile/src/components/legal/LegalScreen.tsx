import { StyleSheet, View } from 'react-native';

import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { legalUpdatedAt, type LegalSection } from '@/src/legal/legalContent';
import { colors, spacing } from '@/src/theme';

type LegalScreenProps = {
  title: string;
  subtitle: string;
  sections: LegalSection[];
};

export function LegalScreen({ title, subtitle, sections }: LegalScreenProps) {
  return (
    <AppScreen>
      <AppHeader title={title} subtitle={subtitle} eyebrow="Legal" />

      <AppCard style={styles.summary}>
        <AppText variant="caption" color={colors.text.secondary}>
          Last updated: {legalUpdatedAt}
        </AppText>
        <AppText color={colors.text.secondary}>
          This summary is provided in the app for convenience. The full website version applies to website, mobile app, ordering, payment, delivery, and support services.
        </AppText>
      </AppCard>

      <View style={styles.sections}>
        {sections.map((section) => (
          <AppCard key={section.title} style={styles.section}>
            <AppText variant="title">{section.title}</AppText>
            {section.body.map((paragraph) => (
              <AppText key={paragraph} color={colors.text.secondary}>
                {paragraph}
              </AppText>
            ))}
          </AppCard>
        ))}
      </View>
    </AppScreen>
  );
}

const styles = StyleSheet.create({
  summary: {
    gap: spacing.sm,
  },
  sections: {
    gap: spacing.lg,
  },
  section: {
    gap: spacing.md,
  },
});
