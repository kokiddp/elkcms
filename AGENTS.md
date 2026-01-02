# ELKCMS - Agent Implementation Guide

This document provides comprehensive instructions for AI agents implementing ELKCMS. It defines the development workflow, testing requirements, and documentation maintenance protocols that **MUST** be followed for every phase.

## 🎯 Core Principles

### 1. **Documentation-First Development**
- **ALWAYS** update documentation files after implementing features
- **NEVER** skip documentation updates
- Documentation is as important as code

### 2. **Test-Driven Development**
- Write comprehensive tests for **EVERY** new feature
- Run full test suite **BEFORE** committing
- Aim for 100% test coverage on critical components (Attributes, Reflection)
- Minimum 80% coverage on all other components

### 3. **Sequential Implementation**
- Follow the exact order specified in [DEVELOPMENT_PLAN.md](DEVELOPMENT_PLAN.md)
- Complete each phase fully before moving to the next
- Verify all tests pass before proceeding

### 4. **Incremental Commits**
- Commit after each completed phase
- Use detailed commit messages following the standard format
- Never batch multiple phases into one commit

## 📋 Mandatory Development Workflow

For **EVERY** phase/feature implementation, follow this exact workflow:

### Step 1: Review Requirements
1. Read the phase details in [DEVELOPMENT_PLAN.md](DEVELOPMENT_PLAN.md)
2. Understand all requirements, methods, and testing criteria
3. Check dependencies are met (previous phases complete)
4. Create a todo list with specific tasks using `TodoWrite` tool

### Step 2: Implement Features
1. Create files in the exact order specified in the plan
2. Follow the code examples and specifications exactly
3. Use proper naming conventions (StudlyCase for classes, camelCase for methods)
4. Add proper PHPDoc comments for all public methods
5. Mark todo items as in_progress → completed as you work

### Step 3: Write Comprehensive Tests
1. Create unit tests for each new class/method
2. Create feature tests for workflows (if applicable)
3. Follow test naming convention: `test_{method}_{scenario}_{expected_result}`
4. Aim for 100% code coverage on new features
5. **Test files must be created in the same commit as the feature**

### Step 4: Run Full Test Suite
1. Execute: `docker exec elkcms_app php artisan test`
2. **ALL tests must pass** - no exceptions
3. If tests fail, fix issues before proceeding
4. Verify test count increased appropriately

### Step 5: Update Documentation (MANDATORY)
**This step is CRITICAL and must NEVER be skipped!**

Update these files **AFTER EVERY COMMIT**:

#### 5.1 Update CHANGELOG.md
```markdown
### Phase X.Y: [Feature Name] (YYYY-MM-DD) ✅
- Feature 1 description
- Feature 2 description
- Testing: ✅ X tests passing
```

#### 5.2 Update DEVELOPMENT_PLAN.md
- Change phase status from `📋 PENDING` to `✅ COMPLETED`
- Add commit hash
- Add testing results
- Update "Last Updated" date
- Update "Current Phase" section
- Update Git Commit History table

#### 5.3 Update README.md
- Move completed phase from "In Progress" to "Completed"
- Update progress indicators (✅ 🔄 📋)
- Update any feature descriptions if needed

#### 5.4 Update Project Structure in DEVELOPMENT_PLAN.md
- Mark completed files with ✅
- Update test counts in Quick Reference section

### Step 6: Commit Changes
Use this exact commit format:

```bash
git add -A
git commit -m "{type}: {Short description} (Phase X.Y)

{Detailed description of what was implemented}

## {Section 1}
- ✅ Feature 1
- ✅ Feature 2

## {Section 2}
- Details...

## Testing
- ✅ X tests passing (Y assertions)
- ✅ Full test suite passing

## Documentation Updated
- ✅ CHANGELOG.md
- ✅ DEVELOPMENT_PLAN.md
- ✅ README.md

🦌 Generated with Claude Code

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>"
```

**Commit Types:**
- `feat:` - New feature
- `test:` - Adding tests
- `docs:` - Documentation updates
- `fix:` - Bug fixes
- `refactor:` - Code refactoring
- `perf:` - Performance improvements
- `chore:` - Maintenance tasks

### Step 7: Verify Completion
Before moving to next phase, verify:
- ✅ All files created
- ✅ All tests passing
- ✅ All documentation updated
- ✅ Git committed with proper message
- ✅ No uncommitted changes
- ✅ Todo list cleared for this phase

## 📊 Test Requirements

### Unit Tests (Required for ALL Phases)

**Coverage Requirements:**
- **Attributes & Reflection:** 100% coverage (critical components)
- **Services & Repositories:** 90% coverage
- **Controllers & Views:** 80% coverage
- **Traits & Helpers:** 90% coverage

**Test Organization:**
```
tests/
├── Unit/
│   ├── CMS/
│   │   ├── Attributes/         # 100% coverage required
│   │   ├── Reflection/         # 100% coverage required
│   │   ├── Traits/             # 90% coverage required
│   │   ├── Services/           # 90% coverage required
│   │   └── Repositories/       # 90% coverage required
└── Feature/
    └── CMS/
        ├── ContentManagement/  # 80% coverage required
        ├── Translation/        # 80% coverage required
        ├── Media/              # 80% coverage required
        ├── Admin/              # 80% coverage required
        └── API/                # 80% coverage required
```

**Test Naming Convention:**
```php
public function test_{method_name}_{scenario}_{expected_result}(): void
{
    // Arrange
    $model = new Model();

    // Act
    $result = $model->method();

    // Assert
    $this->assertEquals('expected', $result);
}
```

**Examples:**
```php
test_can_create_content_model_attribute()
test_generate_slug_from_title_returns_lowercase_slug()
test_translate_missing_field_returns_fallback_value()
test_upload_invalid_file_type_throws_exception()
test_field_attribute_validates_sitemap_priority_min()
```

### Feature Tests (Required for Sprints)

**When to Create:**
- After completing a full sprint
- For complete user workflows
- For critical business logic

**Requirements:**
- Test with real database (use transactions)
- Test complete workflows start to finish
- Test authentication and authorization
- Test file uploads with temporary files

**Example:**
```php
public function test_complete_content_creation_workflow(): void
{
    // Create content in default language
    $post = Post::create(['title' => 'Test Post']);

    // Add translation
    $post->setTranslation('title', 'it', 'Test Articolo');

    // Publish content
    $post->publish();

    // Verify on frontend
    $response = $this->get('/post/' . $post->slug);
    $response->assertStatus(200);
    $response->assertSee('Test Post');
}
```

### Integration Tests (Required for Sprint Completion)

Test these complete workflows:

1. **Content Creation Workflow**
2. **Translation Workflow**
3. **Media Upload & Processing Workflow**
4. **Cache Warming & Retrieval Workflow**
5. **SEO Metadata Generation Workflow**

## 📝 Documentation Files to Update

### CHANGELOG.md
**When:** After every commit
**What:** Add entry for completed phase with date, features, testing results

**Format:**
```markdown
### Phase X.Y: [Feature Name] (2026-01-02) ✅
- Feature 1 implemented
- Feature 2 implemented
- Testing: ✅ X tests passing (Y assertions)
```

### DEVELOPMENT_PLAN.md
**When:** After every commit
**What:** Update phase status, add commit hash, update progress

**Changes Required:**
1. Change phase header from `📋 PENDING` to `✅ COMPLETED`
2. Add commit hash after phase title
3. Update testing section with results
4. Add any implementation notes
5. Update "Last Updated" date at bottom
6. Update "Current Phase" at bottom
7. Update Git Commit History table
8. Update Quick Reference test counts

### README.md
**When:** After every commit or major milestone
**What:** Update development progress section

**Changes Required:**
1. Move completed item from "In Progress" to "Completed"
2. Update progress indicators
3. Update any relevant feature descriptions

### Example Documentation Update Flow

**Before Commit:**
```markdown
# DEVELOPMENT_PLAN.md
### 📋 Phase 1.4: Base Content Model & Traits (PENDING)

# CHANGELOG.md
(no entry yet)

# README.md
### In Progress 🔄
- **Phase 1.4:** Base Content Model & Traits
```

**After Commit:**
```markdown
# DEVELOPMENT_PLAN.md
### ✅ Phase 1.4: Base Content Model & Traits (COMPLETED)
**Commit:** `abc123d` - "feat: Implement Base Content Model & Traits (Phase 1.4)"

**Testing:**
- ✅ 30 tests passing (92 assertions)
- ✅ Full test suite: 103 tests passing (301 assertions)

# CHANGELOG.md
### Phase 1.4: Base Content Model & Traits (2026-01-02) ✅
- BaseContent abstract class with status management
- HasTranslations trait with fallback support
- HasSlug trait with auto-generation
- HasSEO trait with Schema.org integration
- OptimizedQueries trait with eager loading
- Testing: ✅ 30 new tests passing (92 assertions)

# README.md
### Completed ✅
- **Phase 1.1:** PHP 8 Attributes System
- **Phase 1.2:** Model Scanner & Reflection System
- **Phase 1.3:** Migration Generator
- **Phase 1.4:** Base Content Model & Traits

### In Progress 🔄
- **Phase 1.5:** Configuration Files
```

## 🔍 Code Quality Requirements

### PHP Code Standards

1. **Follow PSR-12** coding standard
2. **Use strict types:** `declare(strict_types=1);` at top of files
3. **Type hint everything:** Parameters, return types, properties
4. **PHPDoc required** for all public methods
5. **No unused imports**
6. **No commented-out code** in commits

**Example:**
```php
<?php

declare(strict_types=1);

namespace App\CMS\Services;

use App\CMS\ContentModels\BaseContent;
use Illuminate\Support\Collection;

class ContentService
{
    /**
     * Create new content with translations.
     *
     * @param  string  $modelClass  Fully qualified class name
     * @param  array  $data  Content data
     * @param  array  $translations  Translation data by locale
     * @return BaseContent
     */
    public function create(string $modelClass, array $data, array $translations = []): BaseContent
    {
        // Implementation
    }
}
```

### Testing Standards

1. **One assertion per test** when possible
2. **Clear test names** describing what is tested
3. **Arrange-Act-Assert** pattern
4. **No logic in tests** - tests should be simple
5. **Test edge cases** - null values, empty arrays, invalid input

### Git Standards

1. **Atomic commits** - one feature per commit
2. **Descriptive messages** - explain what and why
3. **No merge commits** on main branch
4. **Sign commits** with GPG if possible

## 🚨 Critical Implementation Order

These components **MUST** be implemented in this exact order:

1. ✅ **Attributes** (ContentModel, Field, Relationship, SEO) - Phase 1.1
2. ✅ **ModelScanner** (reads attributes) - Phase 1.2
3. ✅ **MigrationGenerator** (uses ModelScanner output) - Phase 1.3
4. 🔄 **Base traits** (HasTranslations, HasSlug, HasSEO, OptimizedQueries) - Phase 1.4
5. 📋 **BaseContent model** (uses traits) - Phase 1.4
6. 📋 **Configuration files** (cms.php, languages.php) - Phase 1.5
7. 📋 **Artisan commands** (make-model, generate-migrations, cache) - Phase 1.6
8. 📋 **Example models** (Page, Post) - Phase 2
9. 📋 **Translation system** (database, service, middleware) - Phase 3
10. 📋 **Repository pattern** (ContentRepository, etc.) - Phase 4
11. 📋 **Services** (Content, Media, SEO, Cache) - Phase 5
12. 📋 **FormBuilder** (uses ModelScanner) - Phase 6
13. 📋 **Admin Controllers** (uses FormBuilder) - Phase 7
14. 📋 **Admin views** (uses FormBuilder output) - Phase 7
15. 📋 **Frontend system** (displays content) - Phase 8

**Dependencies:**
- Phase 1.4 requires 1.1, 1.2, 1.3
- Phase 2 requires 1.4
- Phase 3 requires 1.4, 2
- Phase 4 requires 3
- Phase 5 requires 4
- Phase 6 requires 1.2
- Phase 7 requires 5, 6
- Phase 8 requires 7

## 🛠️ Development Commands

### Testing Commands
```bash
# Run all tests
docker exec elkcms_app php artisan test

# Run specific test file
docker exec elkcms_app php artisan test tests/Unit/CMS/Attributes/FieldAttributeTest.php

# Run with coverage
docker exec elkcms_app php artisan test --coverage

# Run specific test method
docker exec elkcms_app php artisan test --filter=test_can_create_field_attribute

# Run tests in parallel (faster)
docker exec elkcms_app php artisan test --parallel
```

### Code Quality Commands
```bash
# Format code with Laravel Pint
docker exec elkcms_app ./vendor/bin/pint

# Check code style without fixing
docker exec elkcms_app ./vendor/bin/pint --test

# Run static analysis with Larastan
docker exec elkcms_app ./vendor/bin/phpstan analyse

# Run ESLint on JavaScript
docker exec elkcms_node npm run lint
```

### Cache Commands
```bash
# Clear all caches
docker exec elkcms_app php artisan cache:clear
docker exec elkcms_app php artisan config:clear
docker exec elkcms_app php artisan view:clear
docker exec elkcms_app php artisan route:clear

# Clear compiled files
docker exec elkcms_app php artisan clear-compiled

# Optimize for production
docker exec elkcms_app php artisan optimize
```

### Migration Commands
```bash
# Run migrations
docker exec elkcms_app php artisan migrate

# Rollback last migration
docker exec elkcms_app php artisan migrate:rollback

# Fresh database (WARNING: destroys all data)
docker exec elkcms_app php artisan migrate:fresh

# Fresh database with seeders
docker exec elkcms_app php artisan migrate:fresh --seed
```

## ⚠️ Common Pitfalls to Avoid

### 1. Skipping Documentation Updates
**Problem:** Code is committed without updating CHANGELOG.md, DEVELOPMENT_PLAN.md, README.md
**Solution:** Always update documentation as part of the same commit
**Enforcement:** Create a pre-commit hook that checks for documentation updates

### 2. Incomplete Test Coverage
**Problem:** Features committed without tests
**Solution:** Write tests BEFORE or WITH the feature implementation
**Rule:** No commit without corresponding tests

### 3. Breaking Changes Without Warning
**Problem:** Modifying existing APIs without considering impact
**Solution:** Always check for existing usage before changing public APIs
**Rule:** Backward compatibility is important

### 4. Ignoring Test Failures
**Problem:** Committing code that doesn't pass tests
**Solution:** Fix all test failures before committing
**Rule:** Test suite must be green before any commit

### 5. Vague Commit Messages
**Problem:** Commit messages like "fix bug" or "update code"
**Solution:** Use the detailed commit message format
**Rule:** Commit message should explain WHAT changed and WHY

### 6. Large Uncommitted Changes
**Problem:** Working on multiple features before committing
**Solution:** Commit after each completed phase/feature
**Rule:** Keep commits atomic and focused

### 7. Not Following Implementation Order
**Problem:** Implementing features out of order, breaking dependencies
**Solution:** Strictly follow the order in DEVELOPMENT_PLAN.md
**Rule:** Never skip ahead - complete phases sequentially

## 🔄 When Things Go Wrong

### Test Failures
```bash
# Run tests with verbose output
docker exec elkcms_app php artisan test --verbose

# Run specific failing test
docker exec elkcms_app php artisan test --filter=test_name

# Check for syntax errors
docker exec elkcms_app php artisan code:check
```

### Migration Issues
```bash
# Check migration status
docker exec elkcms_app php artisan migrate:status

# Rollback last batch
docker exec elkcms_app php artisan migrate:rollback

# Rollback specific migration
docker exec elkcms_app php artisan migrate:rollback --step=1

# Reset and re-run all migrations
docker exec elkcms_app php artisan migrate:refresh
```

### Cache Issues
```bash
# Nuclear option - clear everything
docker exec elkcms_app php artisan cache:clear
docker exec elkcms_app php artisan config:clear
docker exec elkcms_app php artisan view:clear
docker exec elkcms_app php artisan route:clear
docker exec elkcms_app composer dump-autoload
```

### Docker Issues
```bash
# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Check logs
docker-compose logs app
docker-compose logs db
```

## ✅ Phase Completion Checklist

Before marking a phase as complete:

- [ ] All files created according to specification
- [ ] All tests written and passing
- [ ] Test coverage meets requirements (80-100%)
- [ ] Full test suite passing
- [ ] Code formatted with Pint
- [ ] No PHPStan errors
- [ ] CHANGELOG.md updated
- [ ] DEVELOPMENT_PLAN.md updated
- [ ] README.md updated
- [ ] Git commit created with proper message
- [ ] No uncommitted changes remaining
- [ ] Todo list cleared

## 📚 Reference Documents

When implementing features, always reference:

1. **[DEVELOPMENT_PLAN.md](DEVELOPMENT_PLAN.md)** - Complete implementation specifications
2. **[README.md](README.md)** - Feature overview and architecture
3. **[DEVELOPMENT.md](DEVELOPMENT.md)** - Development environment setup
4. **[CONTRIBUTING.md](CONTRIBUTING.md)** - Contribution guidelines
5. **[CHANGELOG.md](CHANGELOG.md)** - Change history
6. **This file (AGENTS.md)** - Agent workflow and requirements

## 🎓 Agent Learning Path

For new agents implementing ELKCMS:

### Week 1: Foundation Understanding
1. Read all documentation files
2. Understand the attribute-driven architecture
3. Study completed phases (1.1-1.3)
4. Review test examples
5. Understand the development workflow

### Week 2: First Implementation
1. Complete Phase 1.4 following this guide
2. Write comprehensive tests
3. Update all documentation
4. Commit with proper format
5. Verify everything works

### Week 3: Independent Implementation
1. Complete Phase 1.5 independently
2. Complete Phase 1.6 independently
3. Follow workflow without guidance
4. Maintain documentation discipline

### Week 4: Advanced Features
1. Complete Sprint 1 (Phases 2.1-2.2)
2. Begin Sprint 2 (Translation system)
3. Implement complex features
4. Maintain quality standards

## 🚀 Success Criteria

An agent is successfully implementing ELKCMS when:

1. ✅ All tests pass after every commit
2. ✅ Documentation is always up-to-date
3. ✅ Commits follow the standard format
4. ✅ Code meets quality standards
5. ✅ Phases completed in correct order
6. ✅ Dependencies properly managed
7. ✅ No breaking changes introduced
8. ✅ Features work as specified

## 💡 Pro Tips

1. **Read the plan thoroughly** before starting any phase
2. **Copy code examples exactly** from DEVELOPMENT_PLAN.md
3. **Test frequently** - after every method, after every class
4. **Commit early, commit often** - don't batch changes
5. **Documentation is not optional** - it's mandatory
6. **When in doubt, ask** - don't guess implementation details
7. **Follow naming conventions** - consistency is key
8. **Keep it simple** - don't over-engineer
9. **Trust the plan** - it's been thoroughly designed
10. **Quality over speed** - better to be slow and correct

---

## 📞 Support

If you encounter issues or ambiguities:

1. Check DEVELOPMENT_PLAN.md for detailed specifications
2. Review completed phases for examples
3. Check test files for usage examples
4. Ask for clarification if requirements are unclear
5. Document any deviations or improvements

Remember: **This is a marathon, not a sprint. Quality and consistency matter more than speed.**

Good luck! 🦌

---

**Last Updated:** 2026-01-02
**Current Workflow Version:** 1.0
**Compliance Required:** 100%
